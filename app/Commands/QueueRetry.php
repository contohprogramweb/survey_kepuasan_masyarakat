<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\QueueService;

/**
 * Queue Retry Command
 * 
 * Retry failed jobs from queue
 * Usage: php spark queue:retry [job_id] [--all]
 */
class QueueRetry extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:retry';
    protected $description = 'Retry failed jobs dari queue';
    protected $usage = 'queue:retry [job_id] [--all]';
    protected $arguments = [
        'job_id' => 'ID job yang akan di-retry (opsional jika --all)',
    ];
    protected $options = [
        '--all' => 'Retry semua failed jobs',
        '--queue' => 'Filter berdasarkan nama queue',
    ];

    protected QueueService $queueService;

    public function run(array $params = [])
    {
        $this->queueService = service('queue');
        
        $jobId = array_shift($params) ?? null;
        $retryAll = CLI::getOption('all') !== null;
        $queueFilter = CLI::getOption('queue');

        if (!$jobId && !$retryAll) {
            CLI::error('Error: Specify a job_id or use --all flag');
            CLI::write('Usage: php spark queue:retry [job_id] [--all]');
            return;
        }

        if ($retryAll) {
            $this->retryAllJobs($queueFilter);
        } else {
            $this->retrySingleJob($jobId);
        }
    }

    /**
     * Retry a single job
     */
    protected function retrySingleJob(string $jobId): void
    {
        CLI::write("Retrying job: {$jobId}", 'cyan');

        $result = $this->queueService->retry($jobId);

        if ($result) {
            CLI::success("Job {$jobId} successfully queued for retry");
        } else {
            CLI::error("Failed to retry job {$jobId}. Job may not exist in failed_jobs queue.");
        }
    }

    /**
     * Retry all failed jobs
     */
    protected function retryAllJobs(?string $queueFilter = null): void
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('tb_queue_jobs')
            ->where('status', 'failed');
        
        if ($queueFilter) {
            $query->where('queue_name', $queueFilter);
        }

        $failedJobs = $query->get()->getResultArray();

        if (empty($failedJobs)) {
            CLI::write('No failed jobs found.', 'yellow');
            return;
        }

        CLI::write("Found " . count($failedJobs) . " failed jobs to retry.", 'cyan');

        $successCount = 0;
        $failCount = 0;

        foreach ($failedJobs as $job) {
            // Extract job_id from payload
            $payload = json_decode($job['payload'], true);
            $jobId = $payload['job_id'] ?? null;

            if (!$jobId) {
                CLI::write("Skipping job with missing job_id", 'yellow');
                $failCount++;
                continue;
            }

            CLI::write("Retrying job: {$jobId}...", 'cyan');

            $result = $this->queueService->retry($jobId);

            if ($result) {
                CLI::write("  ✓ Job {$jobId} queued for retry", 'green');
                $successCount++;
            } else {
                CLI::write("  ✗ Failed to retry job {$jobId}", 'red');
                $failCount++;
            }
        }

        CLI::newLine();
        CLI::write("Retry Summary:", 'bold');
        CLI::write("  Total: " . count($failedJobs));
        CLI::write("  Success: {$successCount}", 'green');
        CLI::write("  Failed: {$failCount}", 'red');
    }
}
