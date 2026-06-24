<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LaporanService;
use App\Jobs\PdfJob;
use App\Jobs\ExcelJob;
use Exception;

/**
 * LaporanController
 * 
 * Controller untuk mengelola laporan IKM (PDF dan Excel)
 * Berdasarkan SRS F-08 dan F-09
 */
class LaporanController extends BaseController
{
    protected LaporanService $laporanService;
    protected PdfJob $pdfJob;
    protected ExcelJob $excelJob;

    public function __construct()
    {
        $this->laporanService = new LaporanService();
        $this->pdfJob = new PdfJob();
        $this->excelJob = new ExcelJob();
        
        // Require authentication
        $this->requireAuth();
    }

    /**
     * Display halaman utama laporan
     */
    public function index()
    {
        $this->requirePermission(['laporan.view', 'admin']);

        $data = [
            'title' => 'Laporan IKM',
            'units' => $this->getUnits(),
            'periodes' => $this->getPeriodes(),
        ];

        return view('app/Views/laporan/index', $data);
    }

    /**
     * Preview laporan PDF (tanpa queue, untuk data kecil)
     */
    public function previewPdf()
    {
        $this->requirePermission(['laporan.view', 'admin']);

        try {
            $filters = $this->getFiltersFromRequest();
            $html = $this->laporanService->generatePdfHtml($filters);

            return $this->response->setBody($html)
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal generate preview: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF via Queue Job (untuk data besar)
     */
    public function generatePdf()
    {
        $this->requirePermission(['laporan.export', 'admin']);

        try {
            $filters = $this->getFiltersFromRequest();

            // Dispatch to queue
            $payload = [
                'report_type' => 'ikm',
                'filters' => $filters,
                'requested_by' => $this->user['id'] ?? null,
            ];

            $jobDispatched = $this->pdfJob->dispatch($payload);

            if ($jobDispatched) {
                return $this->respondJSON([
                    'message' => 'Permintaan generate PDF sedang diproses',
                    'job_type' => 'pdf'
                ], 200, 'success');
            } else {
                return $this->respondError('Gagal menambahkan job ke queue', 500);
            }
        } catch (Exception $e) {
            log_message('error', '[LaporanController] Generate PDF error: ' . $e->getMessage());
            return $this->respondError('Terjadi kesalahan saat memproses permintaan', 500);
        }
    }

    /**
     * Generate Excel via Queue Job (untuk data besar)
     */
    public function generateExcel()
    {
        $this->requirePermission(['laporan.export', 'admin']);

        try {
            $filters = $this->getFiltersFromRequest();

            // Dispatch to queue
            $payload = [
                'report_type' => 'ikm',
                'filters' => $filters,
                'requested_by' => $this->user['id'] ?? null,
            ];

            $jobDispatched = $this->excelJob->dispatch($payload);

            if ($jobDispatched) {
                return $this->respondJSON([
                    'message' => 'Permintaan generate Excel sedang diproses',
                    'job_type' => 'excel'
                ], 200, 'success');
            } else {
                return $this->respondError('Gagal menambahkan job ke queue', 500);
            }
        } catch (Exception $e) {
            log_message('error', '[LaporanController] Generate Excel error: ' . $e->getMessage());
            return $this->respondError('Terjadi kesalahan saat memproses permintaan', 500);
        }
    }

    /**
     * Download file yang sudah jadi
     */
    public function download(string $type, string $filename)
    {
        $this->requirePermission(['laporan.export', 'admin']);

        try {
            $filepath = WRITEPATH . 'exports/' . $filename;

            if (!file_exists($filepath)) {
                return redirect()->back()
                    ->with('error', 'File tidak ditemukan atau belum selesai diproses');
            }

            if ($type === 'pdf') {
                return $this->response->download($filepath, true)
                    ->setContentType('application/pdf');
            } elseif ($type === 'xlsx') {
                return $this->response->download($filepath, true)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            } else {
                return redirect()->back()
                    ->with('error', 'Tipe file tidak valid');
            }
        } catch (Exception $e) {
            log_message('error', '[LaporanController] Download error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengunduh file');
        }
    }

    /**
     * Check status job
     */
    public function jobStatus(string $jobId)
    {
        $this->requirePermission(['laporan.view', 'admin']);

        try {
            // Cek di database untuk status job
            $db = \Config\Database::connect();
            $job = $db->table('tb_queue_jobs')
                ->where('payload LIKE', '%' . $jobId . '%')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$job) {
                return $this->respondError('Job tidak ditemukan', 404);
            }

            return $this->respondJSON([
                'job_id' => $jobId,
                'status' => $job['status'],
                'result' => $job['result'] ?? null,
                'error_message' => $job['error_message'] ?? null,
                'created_at' => $job['created_at'],
                'completed_at' => $job['completed_at'] ?? null,
            ]);
        } catch (Exception $e) {
            return $this->respondError('Gagal mengambil status job', 500);
        }
    }

    /**
     * Direct export Excel tanpa queue (untuk data kecil)
     */
    public function directExcel()
    {
        $this->requirePermission(['laporan.export', 'admin']);

        try {
            $filters = $this->getFiltersFromRequest();
            $filepath = $this->laporanService->generateExcel($filters);

            if (file_exists($filepath)) {
                return $this->response->download($filepath, true)
                    ->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal generate file Excel');
            }
        } catch (Exception $e) {
            log_message('error', '[LaporanController] Direct Excel error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Direct export PDF tanpa queue (untuk data kecil)
     */
    public function directPdf()
    {
        $this->requirePermission(['laporan.export', 'admin']);

        try {
            $filters = $this->getFiltersFromRequest();
            
            // Get HTML
            $html = $this->laporanService->generatePdfHtml($filters);
            
            // Generate PDF using DomPDF
            $dompdf = new \Dompdf\Dompdf();
            $options = new \Dompdf\Options();
            $options->setIsRemoteEnabled(true);
            $options->setDefaultFont('Arial');
            $options->setIsPhpEnabled(true);
            $dompdf->setOptions($options);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Return as download
            $filename = 'Laporan_IKM_' . date('Y-m-d_His') . '.pdf';
            
            return $this->response->setBody($dompdf->output())
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (Exception $e) {
            log_message('error', '[LaporanController] Direct PDF error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get list of completed reports for download
     */
    public function history()
    {
        $this->requirePermission(['laporan.view', 'admin']);

        try {
            $db = \Config\Database::connect();
            
            $reports = $db->table('tb_queue_jobs')
                ->select('id, queue_name, payload, status, result, created_at, completed_at')
                ->whereIn('queue_name', ['pdf-generation', 'excel-generation'])
                ->orderBy('created_at', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();

            // Parse payload and result
            foreach ($reports as &$report) {
                $report['payload_data'] = json_decode($report['payload'], true);
                $report['result_data'] = $report['result'] ? json_decode($report['result'], true) : null;
            }

            return $this->respondJSON($reports);
        } catch (Exception $e) {
            return $this->respondError('Gagal mengambil riwayat laporan', 500);
        }
    }

    /**
     * Helper: Get filters from request
     */
    protected function getFiltersFromRequest(): array
    {
        return [
            'id_unit' => $this->request->getVar('id_unit') ?: null,
            'id_periode' => $this->request->getVar('id_periode') ?: null,
            'start_date' => $this->request->getVar('start_date') ?: null,
            'end_date' => $this->request->getVar('end_date') ?: null,
            'report_type' => $this->request->getVar('report_type') ?: 'ikm',
        ];
    }

    /**
     * Helper: Get units for dropdown
     */
    protected function getUnits(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tb_unit_layanan')
            ->where('is_active', 1)
            ->orderBy('nama_unit', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Helper: Get periodes for dropdown
     */
    protected function getPeriodes(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tb_periode')
            ->where('is_published', 1)
            ->orderBy('tahun', 'DESC')
            ->orderBy('urutan', 'DESC')
            ->get()
            ->getResultArray();
    }
}
