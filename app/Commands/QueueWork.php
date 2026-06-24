<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\QueueService;
use App\Jobs\KalkulasiIKMJob;

/**
 * Queue Work Command
 * 
 * Daemon worker untuk memproses jobs dari database queue
 * Usage: php spark queue:work [queue_name] [--daemon]
 */
class QueueWork extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:work';
    protected $description = 'Menjalankan worker untuk memproses jobs dari database queue';
    protected $usage = 'queue:work [queue_name] [--daemon]';
    protected $arguments = [
        'queue_name' => 'Nama queue yang akan diproses (default: ikm-calculation)',
    ];
    protected $options = [
        '--daemon' => 'Jalankan sebagai daemon (terus menerus)',
        '--timeout' => 'Timeout dalam detik untuk setiap job (default: 300)',
    ];

    protected QueueService $queueService;
    protected string $workerId;

    public function run(array $params = [])
    {
        $this->queueService = service('queue');
        
        // Get queue name from params or use default
        $queueName = array_shift($params) ?? 'ikm-calculation';
        $isDaemon = CLI::getOption('daemon') !== null;
        $timeout = CLI::getOption('timeout') ?? 300;

        // Generate unique worker ID
        $this->workerId = gethostname() . '_' . getmypid() . '_' . time();

        CLI::write("Starting queue worker...", 'green');
        CLI::write("Worker ID: {$this->workerId}");
        CLI::write("Queue: {$queueName}");
        CLI::write("Mode: " . ($isDaemon ? 'Daemon (continuous)' : 'Single run'));
        CLI::newLine();

        // Register worker
        $this->queueService->registerWorker($this->workerId, $queueName);

        // Set up signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        $jobsProcessed = 0;
        $startTime = microtime(true);

        do {
            // Update heartbeat
            $this->queueService->updateWorkerHeartbeat($this->workerId);

            // Check if queue is paused
            if ($this->queueService->isPaused($queueName)) {
                CLI::write("Queue {$queueName} is paused. Waiting...", 'yellow');
                sleep(5);
                continue;
            }

            // Pop job from database queue
            $job = $this->queueService->blpop($queueName, 5);

            if ($job) {
                $jobId = $job['job_id'] ?? 'unknown';
                CLI::write("Processing job: {$jobId}", 'cyan');

                try {
                    // Process the job based on queue type
                    $result = $this->processJob($job, $queueName);

                    // Mark as completed
                    $this->queueService->complete($jobId, $queueName, $result);
                    CLI::write("Job {$jobId} completed successfully", 'green');
                    $jobsProcessed++;
                } catch (\Exception $e) {
                    $attempts = ($job['attempts'] ?? 0) + 1;
                    
                    if ($attempts >= ($job['max_attempts'] ?? 3)) {
                        $this->queueService->fail($jobId, $queueName, $e->getMessage(), $attempts);
                        CLI::write("Job {$jobId} failed after {$attempts} attempts: " . $e->getMessage(), 'red');
                    } else {
                        // Re-queue for retry
                        $job['attempts'] = $attempts;
                        $this->queueService->push($queueName, $job['payload']);
                        CLI::write("Job {$jobId} failed, will retry (attempt {$attempts}): " . $e->getMessage(), 'yellow');
                    }
                }
            }

            // Check memory usage and restart if too high
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = 256 * 1024 * 1024; // 256MB
            
            if ($memoryUsage > $memoryLimit) {
                CLI::write("Memory limit reached. Restarting worker...", 'yellow');
                break;
            }

            // If not daemon mode, exit after processing one job
            if (!$isDaemon) {
                break;
            }

            // Handle signals
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

        } while ($isDaemon);

        // Unregister worker
        $this->queueService->unregisterWorker($this->workerId);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        CLI::newLine();
        CLI::write("Worker stopped.", 'yellow');
        CLI::write("Jobs processed: {$jobsProcessed}");
        CLI::write("Duration: {$duration} seconds");
    }

    /**
     * Process job based on queue type
     */
    protected function processJob(array $job, string $queueName): array
    {
        switch ($queueName) {
            case 'ikm-calculation':
                return $this->processIkmCalculation($job);
            
            case 'notification':
                return $this->processNotification($job);
            
            case 'backup':
                return $this->processBackup($job);
            
            case 'report':
                return $this->processReport($job);
            
            case 'cleanup':
                return $this->processCleanup($job);
            
            default:
                throw new \Exception("Unknown queue type: {$queueName}");
        }
    }

    /**
     * Process IKM calculation job
     */
    protected function processIkmCalculation(array $job): array
    {
        $ikmJob = new KalkulasiIKMJob();
        $payload = $job['payload'] ?? [];
        
        return $ikmJob->process($payload);
    }

    /**
     * Process notification job
     */
    protected function processNotification(array $job): array
    {
        $payload = $job['payload'] ?? [];
        
        // Log notification (in real implementation, send email/SMS/push)
        log_message('info', "[Notification] Type: " . ($payload['type'] ?? 'unknown'));
        log_message('info', "[Notification] Priority: " . ($payload['priority'] ?? 'normal'));
        log_message('info', "[Notification] Message: " . ($payload['data']['message'] ?? ''));

        return [
            'status' => 'sent',
            'notification_type' => $payload['type'] ?? 'unknown',
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Process backup job
     */
    protected function processBackup(array $job): array
    {
        $payload = $job['payload'] ?? [];
        
        // Placeholder for backup logic
        log_message('info', "[Backup] Starting backup: " . json_encode($payload));

        return [
            'status' => 'completed',
            'backup_type' => $payload['type'] ?? 'full',
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Process report generation job
     */
    protected function processReport(array $job): array
    {
        $payload = $job['payload'] ?? [];
        
        // Placeholder for report generation logic
        log_message('info', "[Report] Generating report: " . json_encode($payload));

        return [
            'status' => 'generated',
            'report_type' => $payload['type'] ?? 'summary',
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Process cleanup job
     */
    protected function processCleanup(array $job): array
    {
        $payload = $job['payload'] ?? [];
        
        // Placeholder for cleanup logic
        log_message('info', "[Cleanup] Running cleanup: " . json_encode($payload));

        return [
            'status' => 'cleaned',
            'cleanup_type' => $payload['type'] ?? 'expired_data',
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Handle shutdown signals
     */
    public function handleSignal($signal)
    {
        CLI::newLine();
        CLI::write("Received signal {$signal}. Shutting down gracefully...", 'yellow');
        
        // Unregister worker
        $this->queueService->unregisterWorker($this->workerId);
        
        exit(0);
    }
}
