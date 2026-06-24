<?php

namespace App\Jobs;

use Exception;

/**
 * PdfJob
 * 
 * Job untuk generate laporan PDF menggunakan DomPDF 3.0
 * Diproses melalui database queue untuk data besar (SRS F-08)
 */
class PdfJob
{
    protected const QUEUE_NAME = 'pdf-generation';

    /**
     * Dispatch job to database queue
     * 
     * @param array $payload Data untuk generate PDF
     * @return bool True jika berhasil dispatch
     */
    public function dispatch(array $payload): bool
    {
        try {
            $db = \Config\Database::connect();
            
            $jobData = [
                'job_id' => uniqid('pdf_', true),
                'queue_name' => self::QUEUE_NAME,
                'job_class' => self::class,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'available_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Insert to database queue
            $db->table('tb_queue_jobs')->insert($jobData);

            log_message('info', "[PdfJob] Job {$jobData['job_id']} dispatched to database queue");

            return true;
        } catch (Exception $e) {
            log_message('error', '[PdfJob] Failed to dispatch: ' . $e->getMessage());
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
            $db = \Config\Database::connect();
            
            // Get oldest pending job
            $jobRow = $db->table('tb_queue_jobs')
                ->where('queue_name', self::QUEUE_NAME)
                ->where('status', 'pending')
                ->where('available_at <=', date('Y-m-d H:i:s'))
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($jobRow) {
                // Update status to processing
                $db->table('tb_queue_jobs')
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
            log_message('error', '[PdfJob] Failed to pop: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process the PDF generation
     * 
     * @param array $payload Job payload
     * @return array Result with file path or error
     * @throws Exception
     */
    public function process(array $payload): array
    {
        $reportType = $payload['report_type'] ?? 'ikm';
        $filters = $payload['filters'] ?? [];
        $jobId = $payload['job_id'] ?? uniqid('pdf_', true);

        // Update job status to processing
        $this->updateJobStatus($jobId, 'processing');

        try {
            // Get HTML content from service
            $laporanService = new \App\Services\LaporanService();
            $html = $laporanService->generatePdfHtml($filters);

            // Generate PDF using DomPDF 3.0
            $dompdf = new \Dompdf\Dompdf();
            
            // Load options
            $options = new \Dompdf\Options();
            $options->setIsRemoteEnabled(true);
            $options->setDefaultFont('Arial');
            $options->setIsPhpEnabled(true);
            
            $dompdf->setOptions($options);
            
            // Load HTML
            $dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $dompdf->render();
            
            // Check for rendering errors
            if ($dompdf->get_canvas()->get_cpdf()->numFailures > 0) {
                throw new Exception('PDF rendering failed with errors');
            }
            
            // Save PDF to file
            $filename = 'Laporan_IKM_' . date('Y-m-d_His') . '_' . $jobId . '.pdf';
            $filepath = WRITEPATH . 'exports/' . $filename;
            
            // Ensure directory exists
            if (!is_dir(WRITEPATH . 'exports')) {
                mkdir(WRITEPATH . 'exports', 0755, true);
            }
            
            // Save file
            file_put_contents($filepath, $dompdf->output());
            
            // Update job status to completed
            $this->updateJobStatus($jobId, 'completed', $filepath);
            
            log_message('info', "[PdfJob] PDF generated successfully: {$filepath}");
            
            return [
                'status' => 'success',
                'job_id' => $jobId,
                'file_path' => $filepath,
                'file_name' => $filename,
                'file_size' => filesize($filepath),
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            // Update job status to failed
            $this->updateJobStatus($jobId, 'failed', null, $e->getMessage());
            
            log_message('error', '[PdfJob] Generation failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'job_id' => $jobId,
                'message' => $e->getMessage(),
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
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
     * Get queue size from database
     */
    public function getQueueSize(): int
    {
        try {
            $db = \Config\Database::connect();
            return (int)$db->table('tb_queue_jobs')
                ->where('queue_name', self::QUEUE_NAME)
                ->where('status', 'pending')
                ->countAllResults();
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
            $db = \Config\Database::connect();
            $db->table('tb_queue_jobs')
                ->where('queue_name', self::QUEUE_NAME)
                ->where('status', 'pending')
                ->delete();
        } catch (Exception $e) {
            log_message('error', '[PdfJob] Failed to clear queue: ' . $e->getMessage());
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
