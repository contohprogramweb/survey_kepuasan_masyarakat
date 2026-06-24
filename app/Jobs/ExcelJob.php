<?php

namespace App\Jobs;

use Predis\Client as RedisClient;
use Exception;

/**
 * ExcelJob
 * 
 * Job untuk generate laporan Excel menggunakan PhpSpreadsheet 2.x
 * Diproses melalui queue untuk data besar (SRS F-09)
 */
class ExcelJob
{
    protected RedisClient $redis;
    protected const QUEUE_NAME = 'excel-generation';

    public function __construct()
    {
        $config = config('Redis');
        $this->redis = new RedisClient([
            'scheme' => $config->scheme ?? 'tcp',
            'host' => $config->host ?? '127.0.0.1',
            'port' => $config->port ?? 6379,
            'password' => $config->password ?? null,
            'database' => $config->database ?? 0,
        ]);
    }

    /**
     * Dispatch job to Redis queue
     * 
     * @param array $payload Data untuk generate Excel
     * @return bool True jika berhasil dispatch
     */
    public function dispatch(array $payload): bool
    {
        try {
            $jobData = [
                'job_id' => uniqid('excel_', true),
                'queue' => self::QUEUE_NAME,
                'payload' => $payload,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];

            // Push to Redis queue
            $this->redis->rpush(
                self::QUEUE_NAME,
                json_encode($jobData, JSON_UNESCAPED_UNICODE)
            );

            // Persist to database for tracking
            $this->persistToDatabase($jobData);

            log_message('info', "[ExcelJob] Job {$jobData['job_id']} dispatched to queue");

            return true;
        } catch (Exception $e) {
            log_message('error', '[ExcelJob] Failed to dispatch: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pop job from queue for processing
     * 
     * @return array|null Job data or null if queue empty
     */
    public function pop(): ?array
    {
        try {
            $jobData = $this->redis->lpop(self::QUEUE_NAME);

            if ($jobData) {
                return json_decode($jobData, true);
            }

            return null;
        } catch (Exception $e) {
            log_message('error', '[ExcelJob] Failed to pop: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process the Excel generation
     * 
     * @param array $payload Job payload
     * @return array Result with file path or error
     * @throws Exception
     */
    public function process(array $payload): array
    {
        $reportType = $payload['report_type'] ?? 'ikm';
        $filters = $payload['filters'] ?? [];
        $jobId = $payload['job_id'] ?? uniqid('excel_', true);

        // Update job status to processing
        $this->updateJobStatus($jobId, 'processing');

        try {
            // Generate Excel using service
            $laporanService = new \App\Services\LaporanService();
            $filepath = $laporanService->generateExcel($filters);

            // Check if file exists
            if (!file_exists($filepath)) {
                throw new Exception('Excel file was not created');
            }

            // Update job status to completed
            $this->updateJobStatus($jobId, 'completed', $filepath);

            log_message('info', "[ExcelJob] Excel generated successfully: {$filepath}");

            return [
                'status' => 'success',
                'job_id' => $jobId,
                'file_path' => $filepath,
                'file_name' => basename($filepath),
                'file_size' => filesize($filepath),
                'generated_at' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            // Update job status to failed
            $this->updateJobStatus($jobId, 'failed', null, $e->getMessage());

            log_message('error', '[ExcelJob] Generation failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'job_id' => $jobId,
                'message' => $e->getMessage(),
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Persist job to database for tracking
     */
    protected function persistToDatabase(array $jobData): void
    {
        $db = \Config\Database::connect();

        $data = [
            'queue_name' => self::QUEUE_NAME,
            'job_class' => self::class,
            'payload' => json_encode($jobData['payload'], JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'available_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('tb_queue_jobs')->insert($data);
    }

    /**
     * Update job status in database
     */
    protected function updateJobStatus(string $jobId, string $status, ?string $result = null, ?string $errorMessage = null): void
    {
        $db = \Config\Database::connect();
        
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($result !== null) {
            $data['result'] = $result;
        }
        
        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }
        
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'failed') {
            $data['failed_at'] = date('Y-m-d H:i:s');
        }
        
        $db->table('tb_queue_jobs')
            ->where('payload LIKE', '%' . $jobId . '%')
            ->update($data);
    }

    /**
     * Get queue size
     */
    public function getQueueSize(): int
    {
        try {
            return (int)$this->redis->llen(self::QUEUE_NAME);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Clear queue (for maintenance)
     */
    public function clearQueue(): void
    {
        try {
            $this->redis->del(self::QUEUE_NAME);
        } catch (Exception $e) {
            log_message('error', '[ExcelJob] Failed to clear queue: ' . $e->getMessage());
        }
    }

    /**
     * Get job status by ID
     */
    public function getJobStatus(string $jobId): ?array
    {
        $db = \Config\Database::connect();
        
        $job = $db->table('tb_queue_jobs')
            ->where('payload LIKE', '%' . $jobId . '%')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        return $job ?: null;
    }
}
