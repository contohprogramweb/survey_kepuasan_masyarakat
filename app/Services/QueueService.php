<?php

namespace App\Services;

use Predis\Client as RedisClient;
use Exception;
use Config\Queue as QueueConfig;

/**
 * QueueService
 * 
 * Service untuk mengelola Redis Queue dengan multi-queue support
 * Mendukung queue: ikm-calculation, notification, backup, report, cleanup
 */
class QueueService
{
    protected RedisClient $redis;
    protected QueueConfig $config;
    protected string $prefix;

    /**
     * Daftar queue yang tersedia
     */
    protected array $queues = [
        'ikm-calculation',
        'notification',
        'backup',
        'report',
        'cleanup'
    ];

    public function __construct(?QueueConfig $config = null)
    {
        $this->config = $config ?? config('Queue');
        $this->prefix = $this->config->prefix ?? 'ikm_queue_';
        
        $redisConfig = config('Redis') ?? (object)[
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0,
        ];

        $this->redis = new RedisClient([
            'scheme' => $redisConfig->scheme ?? 'tcp',
            'host' => $redisConfig->host ?? '127.0.0.1',
            'port' => $redisConfig->port ?? 6379,
            'password' => $redisConfig->password ?? null,
            'database' => $redisConfig->database ?? 0,
            'timeout' => 5.0,
        ]);
    }

    /**
     * Push job ke queue tertentu
     *
     * @param string $queueName Nama queue
     * @param array $jobData Data job
     * @return bool True jika berhasil
     */
    public function push(string $queueName, array $jobData): bool
    {
        if (!in_array($queueName, $this->queues)) {
            log_message('error', "[QueueService] Queue '{$queueName}' tidak terdaftar.");
            return false;
        }

        try {
            $fullQueueName = $this->prefix . $queueName;
            
            $job = [
                'job_id' => uniqid('job_', true),
                'queue' => $queueName,
                'payload' => $jobData,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
            ];

            // Push ke Redis list (RPUSH untuk FIFO)
            $this->redis->rpush($fullQueueName, json_encode($job, JSON_UNESCAPED_UNICODE));
            
            // Update statistik queue
            $this->incrementCounter($queueName . ':total_pushed');
            
            // Persist ke database sebagai fallback
            $this->persistToDatabase($job, $queueName);

            log_message('info', "[QueueService] Job {$job['job_id']} dipush ke queue {$queueName}");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to push job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pop job dari queue untuk diproses
     *
     * @param string $queueName Nama queue
     * @return array|null Job data atau null jika kosong
     */
    public function pop(string $queueName): ?array
    {
        if (!in_array($queueName, $this->queues)) {
            return null;
        }

        try {
            $fullQueueName = $this->prefix . $queueName;
            $jobData = $this->redis->lpop($fullQueueName);

            if ($jobData) {
                $job = json_decode($jobData, true);
                if ($job) {
                    // Set status processing
                    $this->setJobStatus($job['job_id'], 'processing', $queueName);
                    return $job;
                }
            }

            return null;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to pop job: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Block dan pop job dari queue (untuk worker daemon)
     *
     * @param string $queueName Nama queue
     * @param int $timeout Timeout dalam detik
     * @return array|null Job data atau null jika timeout
     */
    public function blpop(string $queueName, int $timeout = 5): ?array
    {
        if (!in_array($queueName, $this->queues)) {
            return null;
        }

        try {
            $fullQueueName = $this->prefix . $queueName;
            $result = $this->redis->blpop($fullQueueName, $timeout);

            if ($result && is_array($result) && count($result) >= 2) {
                $jobData = $result[1];
                $job = json_decode($jobData, true);
                if ($job) {
                    $this->setJobStatus($job['job_id'], 'processing', $queueName);
                    return $job;
                }
            }

            return null;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to blpop job: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark job sebagai completed
     *
     * @param string $jobId ID job
     * @param string $queueName Nama queue
     * @param array $result Hasil eksekusi
     * @return bool
     */
    public function complete(string $jobId, string $queueName, array $result = []): bool
    {
        try {
            $this->setJobStatus($jobId, 'completed', $queueName, $result);
            $this->incrementCounter($queueName . ':total_completed');
            log_message('info', "[QueueService] Job {$jobId} completed");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to complete job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark job sebagai failed
     *
     * @param string $jobId ID job
     * @param string $queueName Nama queue
     * @param string $errorMessage Pesan error
     * @param int $attempts Jumlah percobaan
     * @return bool
     */
    public function fail(string $jobId, string $queueName, string $errorMessage, int $attempts = 1): bool
    {
        try {
            $this->setJobStatus($jobId, 'failed', $queueName, ['error' => $errorMessage, 'attempts' => $attempts]);
            $this->incrementCounter($queueName . ':total_failed');
            
            // Simpan ke failed jobs queue untuk retry
            $this->redis->rpush($this->prefix . 'failed_jobs', json_encode([
                'job_id' => $jobId,
                'queue' => $queueName,
                'error' => $errorMessage,
                'failed_at' => date('Y-m-d H:i:s'),
                'attempts' => $attempts,
            ]));

            log_message('error', "[QueueService] Job {$jobId} failed: {$errorMessage}");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to mark job as failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retry job yang failed
     *
     * @param string $jobId ID job
     * @return bool True jika berhasil di-retry
     */
    public function retry(string $jobId): bool
    {
        try {
            // Cari job di failed_jobs
            $failedJobs = $this->redis->lrange($this->prefix . 'failed_jobs', 0, -1);
            
            foreach ($failedJobs as $index => $jobData) {
                $job = json_decode($jobData, true);
                if ($job && $job['job_id'] === $jobId) {
                    // Hapus dari failed_jobs
                    $this->redis->lrem($this->prefix . 'failed_jobs', 1, $jobData);
                    
                    // Re-push ke queue asal
                    $queueName = $job['queue'];
                    if (in_array($queueName, $this->queues)) {
                        return $this->push($queueName, [
                            'original_job_id' => $jobId,
                            'retry_count' => ($job['attempts'] ?? 0) + 1,
                        ]);
                    }
                }
            }

            return false;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to retry job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get queue statistics
     *
     * @param string $queueName Nama queue
     * @return array Statistik queue
     */
    public function getStats(string $queueName): array
    {
        if (!in_array($queueName, $this->queues)) {
            return [];
        }

        try {
            $fullQueueName = $this->prefix . $queueName;
            
            return [
                'queue_name' => $queueName,
                'pending' => (int)$this->redis->llen($fullQueueName),
                'processing' => (int)$this->getCounter($queueName . ':processing'),
                'completed' => (int)$this->getCounter($queueName . ':total_completed'),
                'failed' => (int)$this->getCounter($queueName . ':total_failed'),
                'total_pushed' => (int)$this->getCounter($queueName . ':total_pushed'),
            ];
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to get stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all queues statistics
     *
     * @return array Statistik semua queue
     */
    public function getAllStats(): array
    {
        $stats = [];
        foreach ($this->queues as $queueName) {
            $stats[$queueName] = $this->getStats($queueName);
        }
        
        // Tambahkan info failed jobs
        $stats['failed_jobs'] = [
            'count' => (int)$this->redis->llen($this->prefix . 'failed_jobs'),
        ];

        return $stats;
    }

    /**
     * Pause queue (dengan flag Redis)
     *
     * @param string $queueName Nama queue
     * @return bool
     */
    public function pause(string $queueName): bool
    {
        try {
            $this->redis->set($this->prefix . "paused:{$queueName}", '1');
            log_message('info', "[QueueService] Queue {$queueName} paused");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to pause queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume queue
     *
     * @param string $queueName Nama queue
     * @return bool
     */
    public function resume(string $queueName): bool
    {
        try {
            $this->redis->del($this->prefix . "paused:{$queueName}");
            log_message('info', "[QueueService] Queue {$queueName} resumed");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to resume queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if queue is paused
     *
     * @param string $queueName Nama queue
     * @return bool
     */
    public function isPaused(string $queueName): bool
    {
        try {
            return (bool)$this->redis->get($this->prefix . "paused:{$queueName}");
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get worker status (dari Redis)
     *
     * @return array Status worker aktif
     */
    public function getWorkerStatus(): array
    {
        try {
            $workers = [];
            $workerKeys = $this->redis->keys($this->prefix . 'worker:*');
            
            foreach ($workerKeys as $key) {
                $data = $this->redis->get($key);
                if ($data) {
                    $workers[] = json_decode($data, true);
                }
            }

            return $workers;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Register worker heartbeat
     *
     * @param string $workerId ID worker
     * @param string $queueName Queue yang diproses
     * @return void
     */
    public function registerWorker(string $workerId, string $queueName): void
    {
        try {
            $workerData = [
                'worker_id' => $workerId,
                'queue' => $queueName,
                'pid' => getmypid(),
                'started_at' => date('Y-m-d H:i:s'),
                'last_heartbeat' => date('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true),
                'status' => 'active',
            ];

            $key = $this->prefix . "worker:{$workerId}";
            $this->redis->setex($key, 60, json_encode($workerData)); // TTL 60 detik
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to register worker: " . $e->getMessage());
        }
    }

    /**
     * Update worker heartbeat
     *
     * @param string $workerId ID worker
     * @param string $currentJob ID job yang sedang diproses
     * @return void
     */
    public function updateWorkerHeartbeat(string $workerId, string $currentJob = ''): void
    {
        try {
            $key = $this->prefix . "worker:{$workerId}";
            $data = $this->redis->get($key);
            
            if ($data) {
                $workerData = json_decode($data, true);
                $workerData['last_heartbeat'] = date('Y-m-d H:i:s');
                $workerData['current_job'] = $currentJob;
                $workerData['memory_usage'] = memory_get_usage(true);
                
                $this->redis->setex($key, 60, json_encode($workerData));
            }
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to update worker heartbeat: " . $e->getMessage());
        }
    }

    /**
     * Unregister worker
     *
     * @param string $workerId ID worker
     * @return void
     */
    public function unregisterWorker(string $workerId): void
    {
        try {
            $key = $this->prefix . "worker:{$workerId}";
            $this->redis->del($key);
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to unregister worker: " . $e->getMessage());
        }
    }

    /**
     * Increment counter di Redis
     */
    protected function incrementCounter(string $key): void
    {
        $this->redis->incr($this->prefix . $key);
    }

    /**
     * Get counter value dari Redis
     */
    protected function getCounter(string $key): int
    {
        $value = $this->redis->get($this->prefix . $key);
        return $value ? (int)$value : 0;
    }

    /**
     * Set job status di database
     */
    protected function setJobStatus(string $jobId, string $status, string $queueName, array $result = []): void
    {
        $db = \Config\Database::connect();
        
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'completed' || $status === 'failed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
            $data['result'] = !empty($result) ? json_encode($result, JSON_UNESCAPED_UNICODE) : null;
        }

        $db->table('tb_queue_jobs')
            ->where('payload LIKE', "%\"job_id\":\"{$jobId}\"%")
            ->update($data);
    }

    /**
     * Persist job ke database
     */
    protected function persistToDatabase(array $job, string $queueName): void
    {
        $db = \Config\Database::connect();

        $data = [
            'queue_name' => $queueName,
            'job_class' => $job['queue'] . '-job',
            'payload' => json_encode($job['payload'], JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => $job['max_attempts'],
            'available_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('tb_queue_jobs')->insert($data);
    }

    /**
     * Clear queue (untuk maintenance)
     *
     * @param string $queueName Nama queue
     * @return bool
     */
    public function clear(string $queueName): bool
    {
        if (!in_array($queueName, $this->queues)) {
            return false;
        }

        try {
            $fullQueueName = $this->prefix . $queueName;
            $this->redis->del($fullQueueName);
            log_message('info', "[QueueService] Queue {$queueName} cleared");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to clear queue: " . $e->getMessage());
            return false;
        }
    }
}
