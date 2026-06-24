<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\QueueService;

/**
 * QueueController
 * 
 * Controller untuk monitoring dan manajemen queue system
 * Dashboard Queue Monitor:
 * - Jumlah pending, processing, failed, completed
 * - Worker status (active, uptime, memory)
 * - Retry failed jobs
 * - Pause/resume queue
 */
class QueueController extends BaseController
{
    protected QueueService $queueService;

    public function __construct()
    {
        $this->queueService = service('queue');
    }

    /**
     * Dashboard monitoring queue
     * Menampilkan statistik semua queue dan worker
     */
    public function index(): string
    {
        $data = [
            'title' => 'Queue Monitor',
            'queueStats' => $this->queueService->getAllStats(),
            'workers' => $this->queueService->getWorkerStatus(),
            'queues' => ['ikm-calculation', 'notification', 'backup', 'report', 'cleanup'],
        ];

        return view('admin/queue_monitor', $data);
    }

    /**
     * Get queue statistics as JSON (for AJAX polling)
     */
    public function stats(): \CodeIgniter\HTTP\JSONResponse
    {
        return $this->response->setJSON([
            'success' => true,
            'data' => $this->queueService->getAllStats(),
            'workers' => $this->queueService->getWorkerStatus(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Pause a specific queue
     */
    public function pause(string $queueName): \CodeIgniter\HTTP\JSONResponse
    {
        $allowedQueues = ['ikm-calculation', 'notification', 'backup', 'report', 'cleanup'];
        
        if (!in_array($queueName, $allowedQueues)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue name tidak valid'
            ], 400);
        }

        $result = $this->queueService->pause($queueName);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? "Queue '{$queueName}' berhasil di-pause" : "Gagal pause queue '{$queueName}'"
        ]);
    }

    /**
     * Resume a paused queue
     */
    public function resume(string $queueName): \CodeIgniter\HTTP\JSONResponse
    {
        $allowedQueues = ['ikm-calculation', 'notification', 'backup', 'report', 'cleanup'];
        
        if (!in_array($queueName, $allowedQueues)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue name tidak valid'
            ], 400);
        }

        $result = $this->queueService->resume($queueName);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? "Queue '{$queueName}' berhasil di-resume" : "Gagal resume queue '{$queueName}'"
        ]);
    }

    /**
     * Retry a failed job
     */
    public function retry(string $jobId): \CodeIgniter\HTTP\JSONResponse
    {
        if (empty($jobId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Job ID diperlukan'
            ], 400);
        }

        $result = $this->queueService->retry($jobId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Job berhasil di-retry' : 'Gagal retry job'
        ]);
    }

    /**
     * Clear a queue (maintenance only)
     */
    public function clear(string $queueName): \CodeIgniter\HTTP\JSONResponse
    {
        $allowedQueues = ['ikm-calculation', 'notification', 'backup', 'report', 'cleanup'];
        
        if (!in_array($queueName, $allowedQueues)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Queue name tidak valid'
            ], 400);
        }

        // Only allow in development or with special permission
        if (ENVIRONMENT !== 'development') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Clear queue hanya tersedia di environment development'
            ], 403);
        }

        $result = $this->queueService->clear($queueName);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? "Queue '{$queueName}' berhasil di-clear" : "Gagal clear queue '{$queueName}'"
        ]);
    }

    /**
     * Get detailed job information from database
     */
    public function jobDetail(string $jobId): \CodeIgniter\HTTP\JSONResponse
    {
        $db = \Config\Database::connect();
        
        $job = $db->table('tb_queue_jobs')
            ->where('payload LIKE', "%\"job_id\":\"{$jobId}\"%")
            ->get()
            ->getRowArray();

        if (!$job) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Job tidak ditemukan'
            ], 404);
        }

        // Decode payload for display
        if (!empty($job['payload'])) {
            $job['payload_decoded'] = json_decode($job['payload'], true);
        }

        if (!empty($job['result'])) {
            $job['result_decoded'] = json_decode($job['result'], true);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $job
        ]);
    }

    /**
     * List failed jobs
     */
    public function failedJobs(): \CodeIgniter\HTTP\JSONResponse
    {
        $db = \Config\Database::connect();
        
        $failedJobs = $db->table('tb_queue_jobs')
            ->where('status', 'failed')
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        foreach ($failedJobs as &$job) {
            if (!empty($job['payload'])) {
                $job['payload_decoded'] = json_decode($job['payload'], true);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $failedJobs,
            'total' => count($failedJobs)
        ]);
    }
}
