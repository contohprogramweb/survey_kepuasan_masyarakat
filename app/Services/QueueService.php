<?php

namespace App\Services;

use Exception;
use Config\Queue as QueueConfig;
use CodeIgniter\Database\BaseConnection;

/**
 * QueueService
 * 
 * Service untuk mengelola Database Queue dengan multi-queue support
 * Mendukung queue: ikm-calculation, notification, backup, report, cleanup
 * Menggunakan database sebagai pengganti Redis untuk penyimpanan queue
 */
class QueueService
{
    protected BaseConnection $db;
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
        $this->db = \Config\Database::connect();
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
            $job = [
                'job_id' => uniqid('job_', true),
                'queue' => $queueName,
                'payload' => json_encode($jobData, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'available_at' => date('Y-m-d H:i:s'),
            ];

            // Insert ke database table jobs
            $this->db->table('tb_queue_jobs')->insert($job);
            
            // Update statistik counter
            $this->incrementCounter($queueName . ':total_pushed');

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
            // Ambil job tertua yang pending dan available
            $jobRow = $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'pending')
                ->where('available_at <=', date('Y-m-d H:i:s'))
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($jobRow) {
                // Update status ke processing
                $this->db->table('tb_queue_jobs')
                    ->where('id', $jobRow['id'])
                    ->update([
                        'status' => 'processing',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                return [
                    'job_id' => $jobRow['job_id'],
                    'queue' => $jobRow['queue_name'],
                    'payload' => json_decode($jobRow['payload'], true),
                    'attempts' => $jobRow['attempts'],
                    'max_attempts' => $jobRow['max_attempts'],
                    'created_at' => $jobRow['created_at'],
                ];
            }

            return null;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to pop job: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Block dan pop job dari queue (untuk worker daemon)
     * Menggunakan polling dengan interval tertentu
     *
     * @param string $queueName Nama queue
     * @param int $timeout Timeout dalam detik
     * @return array|null Job data atau null jika timeout
     */
    public function blpop(string $queueName, int $timeout = 5): ?array
    {
        $startTime = time();
        
        while ((time() - $startTime) < $timeout) {
            $job = $this->pop($queueName);
            if ($job !== null) {
                return $job;
            }
            // Wait sebelum retry
            usleep(500000); // 500ms
        }
        
        return null;
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
            
            // Simpan ke failed jobs table untuk retry
            $this->db->table('tb_failed_jobs')->insert([
                'job_id' => $jobId,
                'queue_name' => $queueName,
                'error' => $errorMessage,
                'failed_at' => date('Y-m-d H:i:s'),
                'attempts' => $attempts,
            ]);

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
            $failedJob = $this->db->table('tb_failed_jobs')
                ->where('job_id', $jobId)
                ->get()
                ->getRowArray();
            
            if ($failedJob) {
                // Hapus dari failed_jobs
                $this->db->table('tb_failed_jobs')
                    ->where('job_id', $jobId)
                    ->delete();
                
                // Re-push ke queue asal
                $queueName = $failedJob['queue_name'];
                if (in_array($queueName, $this->queues)) {
                    return $this->push($queueName, [
                        'original_job_id' => $jobId,
                        'retry_count' => ($failedJob['attempts'] ?? 0) + 1,
                    ]);
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
            $pending = $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'pending')
                ->countAllResults();
            
            $processing = $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'processing')
                ->countAllResults();
            
            $completed = $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'completed')
                ->countAllResults();
            
            $failed = $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'failed')
                ->countAllResults();
            
            $totalPushed = (int)$this->getCounter($queueName . ':total_pushed');
            
            return [
                'queue_name' => $queueName,
                'pending' => (int)$pending,
                'processing' => (int)$processing,
                'completed' => (int)$completed,
                'failed' => (int)$failed,
                'total_pushed' => $totalPushed,
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
        $failedCount = $this->db->table('tb_failed_jobs')->countAllResults();
        $stats['failed_jobs'] = [
            'count' => (int)$failedCount,
        ];

        return $stats;
    }

    /**
     * Pause queue (dengan flag di database)
     *
     * @param string $queueName Nama queue
     * @return bool
     */
    public function pause(string $queueName): bool
    {
        try {
            $this->db->table('tb_queue_settings')->replace([
                'queue_name' => $queueName,
                'is_paused' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
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
            $this->db->table('tb_queue_settings')->replace([
                'queue_name' => $queueName,
                'is_paused' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
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
            $setting = $this->db->table('tb_queue_settings')
                ->where('queue_name', $queueName)
                ->get()
                ->getRowArray();
            return $setting && $setting['is_paused'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get worker status (dari database)
     *
     * @return array Status worker aktif
     */
    public function getWorkerStatus(): array
    {
        try {
            return $this->db->table('tb_workers')
                ->where('status', 'active')
                ->where('last_heartbeat >=', date('Y-m-d H:i:s', strtotime('-2 minutes')))
                ->get()
                ->getResultArray();
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
            $this->db->table('tb_workers')->replace([
                'worker_id' => $workerId,
                'queue_name' => $queueName,
                'pid' => getmypid(),
                'started_at' => date('Y-m-d H:i:s'),
                'last_heartbeat' => date('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true),
                'status' => 'active',
            ]);
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
            $this->db->table('tb_workers')
                ->where('worker_id', $workerId)
                ->update([
                    'last_heartbeat' => date('Y-m-d H:i:s'),
                    'current_job' => $currentJob,
                    'memory_usage' => memory_get_usage(true),
                ]);
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
            $this->db->table('tb_workers')
                ->where('worker_id', $workerId)
                ->delete();
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to unregister worker: " . $e->getMessage());
        }
    }

    /**
     * Increment counter di database
     */
    protected function incrementCounter(string $key): void
    {
        $this->db->table('tb_queue_counters')->replace([
            'counter_key' => $this->prefix . $key,
            'value' => $this->getCounter($key) + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get counter value dari database
     */
    protected function getCounter(string $key): int
    {
        $row = $this->db->table('tb_queue_counters')
            ->where('counter_key', $this->prefix . $key)
            ->get()
            ->getRowArray();
        return $row ? (int)$row['value'] : 0;
    }

    /**
     * Set job status di database
     */
    protected function setJobStatus(string $jobId, string $status, string $queueName, array $result = []): void
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'completed' || $status === 'failed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
            $data['result'] = !empty($result) ? json_encode($result, JSON_UNESCAPED_UNICODE) : null;
        }

        $this->db->table('tb_queue_jobs')
            ->where('job_id', $jobId)
            ->update($data);
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
            $this->db->table('tb_queue_jobs')
                ->where('queue_name', $queueName)
                ->where('status', 'pending')
                ->delete();
            log_message('info', "[QueueService] Queue {$queueName} cleared");
            return true;
        } catch (Exception $e) {
            log_message('error', "[QueueService] Failed to clear queue: " . $e->getMessage());
            return false;
        }
    }
}
