<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QRCodeModel;
use App\Models\UnitLayananModel;
use App\Services\QRCodeService;
use App\Services\ShortUrlService;

/**
 * QRController - Admin Controller untuk QR Code Generator
 * 
 * Berdasarkan SRS F-14 dan UC-14
 */
class QRController extends BaseController
{
    protected QRCodeModel $qrModel;
    protected UnitLayananModel $unitModel;
    protected QRCodeService $qrService;
    protected ShortUrlService $shortUrlService;
    
    public function __construct()
    {
        $this->qrModel = new QRCodeModel();
        $this->unitModel = new UnitLayananModel();
        $this->qrService = new QRCodeService();
        $this->shortUrlService = new ShortUrlService();
        
        // Pastikan direktori QR Code ada
        if (!is_dir(WRITEPATH . 'qr_codes')) {
            mkdir(WRITEPATH . 'qr_codes', 0755, true);
        }
    }
    
    /**
     * Display QR Code Generator page
     * GET /admin/qr
     */
    public function index()
    {
        $data = [
            'title' => 'QR Code Generator',
            'units' => $this->unitModel->getActiveUnits(),
            'periods' => $this->getAvailablePeriods(),
            'sizePresets' => ['S' => 'Small (200px)', 'M' => 'Medium (300px)', 'L' => 'Large (400px)', 'XL' => 'Extra Large (500px)'],
            'formats' => ['png' => 'PNG Image', 'svg' => 'SVG Vector', 'pdf' => 'PDF Document'],
            'currentLocale' => get_current_locale(),
        ];
        
        return view('admin/qr/generator', $data);
    }
    
    /**
     * Generate QR Code preview
     * POST /admin/qr/preview
     */
    public function preview()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_unit' => 'required|integer',
            'id_periode' => 'permit_empty|integer',
            'format' => 'required|in_list[svg,png,pdf]',
            'size_preset' => 'permit_empty|in_list[S,M,L,XL]',
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
            ]);
        }
        
        $idUnit = $this->request->getPost('id_unit');
        $idPeriode = $this->request->getPost('id_periode');
        $format = $this->request->getPost('format');
        $sizePreset = $this->request->getPost('size_preset') ?? 'M';
        $includeLogo = $this->request->getPost('include_logo') === '1';
        
        // Get unit info
        $unit = $this->unitModel->find($idUnit);
        if (!$unit) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unit layanan tidak ditemukan.',
            ]);
        }
        
        // Build landing page URL
        $landingUrl = site_url("survei/index/{$idUnit}/{$idPeriode}");
        
        // Generate temporary short code for preview
        $tempShortCode = $this->shortUrlService->generateShortCode($landingUrl);
        $trackingUrl = $this->shortUrlService->buildTrackingUrl(
            $tempShortCode,
            $idUnit,
            $idPeriode,
            $this->request->getPost('periode_name') ?? ''
        );
        
        // Get logo path if requested
        $logoPath = null;
        if ($includeLogo && !empty($unit['logo_path']) && file_exists($unit['logo_path'])) {
            $logoPath = $unit['logo_path'];
        }
        
        try {
            // Generate QR Code untuk preview
            $result = $this->qrService->generate(
                $trackingUrl,
                $format,
                $sizePreset,
                $logoPath,
                'Scan untuk Survei'
            );
            
            // Convert ke base64 untuk preview
            $base64 = base64_encode($result['content']);
            
            return $this->response->setJSON([
                'success' => true,
                'preview_url' => 'data:' . $result['mime_type'] . ';base64,' . $base64,
                'tracking_url' => $trackingUrl,
                'landing_url' => $landingUrl,
                'short_code' => $tempShortCode,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[QRController] Preview error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal generate QR Code: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Save QR Code to database and generate file
     * POST /admin/qr/save
     */
    public function save()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_unit' => 'required|integer',
            'id_periode' => 'permit_empty|integer',
            'format' => 'required|in_list[svg,png,pdf]',
            'size_preset' => 'permit_empty|in_list[S,M,L,XL]',
            'short_url' => 'required|alpha_dash|max_length[100]',
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
            ]);
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            $idUnit = $this->request->getPost('id_unit');
            $idPeriode = $this->request->getPost('id_periode');
            $format = $this->request->getPost('format');
            $sizePreset = $this->request->getPost('size_preset') ?? 'M';
            $shortUrl = $this->request->getPost('short_url');
            $includeLogo = $this->request->getPost('include_logo') === '1';
            
            // Check if short URL already exists
            if ($this->qrModel->shortUrlExists($shortUrl)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Short URL sudah digunakan. Gunakan yang lain.',
                ]);
            }
            
            // Get unit info
            $unit = $this->unitModel->find($idUnit);
            if (!$unit) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unit layanan tidak ditemukan.',
                ]);
            }
            
            // Build tracking URL dengan UTM parameters
            $periodeName = $this->request->getPost('periode_name') ?? '';
            $landingUrl = site_url("survei/index/{$idUnit}/{$idPeriode}");
            $trackingUrl = $this->shortUrlService->buildTrackingUrl(
                $shortUrl,
                $idUnit,
                $idPeriode,
                $periodeName
            );
            
            // Get logo path
            $logoPath = null;
            if ($includeLogo && !empty($unit['logo_path']) && file_exists($unit['logo_path'])) {
                $logoPath = $unit['logo_path'];
            }
            
            // Generate QR Code
            $result = $this->qrService->generate(
                $trackingUrl,
                $format,
                $sizePreset,
                $logoPath,
                'Scan untuk Survei'
            );
            
            // Save file
            $filename = 'qr_' . $shortUrl . '.' . $format;
            $filePath = WRITEPATH . 'qr_codes/' . $filename;
            
            if (file_put_contents($filePath, $result['content']) === false) {
                throw new \Exception('Gagal menyimpan file QR Code.');
            }
            
            // Save to database
            $qrData = [
                'id_unit' => $idUnit,
                'id_periode' => $idPeriode,
                'short_url' => $shortUrl,
                'qr_data' => $trackingUrl,
                'format' => $format,
                'file_path' => 'qr_codes/' . $filename,
                'size_preset' => $sizePreset,
                'logo_path' => $includeLogo ? $unit['logo_path'] : null,
                'utm_source' => 'qr',
                'utm_medium' => 'poster',
                'utm_campaign' => !empty($periodeName) ? $this->slugify($periodeName) : 'periode_' . ($idPeriode ?? $idUnit),
                'is_active' => 1,
            ];
            
            $idQr = $this->qrModel->insert($qrData);
            
            if (!$idQr) {
                throw new \Exception('Gagal menyimpan data QR Code ke database.');
            }
            
            $db->transCommit();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'QR Code berhasil dibuat.',
                'id_qr' => $idQr,
                'download_url' => site_url('admin/qr/download/' . $idQr),
                'print_url' => site_url('admin/qr/print/' . $idQr),
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[QRController] Save error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Download QR Code file
     * GET /admin/qr/download/{id}
     */
    public function download(int $idQr)
    {
        $qr = $this->qrModel->find($idQr);
        
        if (!$qr || empty($qr['file_path'])) {
            return redirect()->back()->with('error', 'QR Code tidak ditemukan.');
        }
        
        $filePath = WRITEPATH . $qr['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File QR Code tidak ditemukan.');
        }
        
        return $this->response->download($filePath, true);
    }
    
    /**
     * Print layout page
     * GET /admin/qr/print/{id}
     */
    public function print(int $idQr)
    {
        $qr = $this->qrModel->getWithRelations($idQr);
        
        if (!$qr) {
            return redirect()->back()->with('error', 'QR Code tidak ditemukan.');
        }
        
        $data = [
            'qr' => $qr,
            'title' => 'Cetak QR Code - ' . $qr['nama_unit'],
        ];
        
        return view('admin/qr/print', $data);
    }
    
    /**
     * List all QR Codes
     * GET /admin/qr/list
     */
    public function list()
    {
        $idUnit = $this->request->getGet('id_unit');
        $idPeriode = $this->request->getGet('id_periode');
        
        $data = [
            'title' => 'Daftar QR Code',
            'qrCodes' => $this->qrModel->getWithRelations($idUnit, $idPeriode),
            'units' => $this->unitModel->getActiveUnits(),
        ];
        
        return view('admin/qr/list', $data);
    }
    
    /**
     * Toggle QR Code active status
     * POST /admin/qr/toggle/{id}
     */
    public function toggleStatus(int $idQr)
    {
        $qr = $this->qrModel->find($idQr);
        
        if (!$qr) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.',
            ]);
        }
        
        $newStatus = $qr['is_active'] ? 0 : 1;
        
        $this->qrModel->update($idQr, ['is_active' => $newStatus]);
        
        return $this->response->setJSON([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'QR Code diaktifkan.' : 'QR Code dinonaktifkan.',
        ]);
    }
    
    /**
     * Delete QR Code
     * DELETE /admin/qr/{id}
     */
    public function delete(int $idQr)
    {
        $qr = $this->qrModel->find($idQr);
        
        if (!$qr) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.',
            ]);
        }
        
        // Delete file
        if (!empty($qr['file_path']) && file_exists(WRITEPATH . $qr['file_path'])) {
            unlink(WRITEPATH . $qr['file_path']);
        }
        
        $this->qrModel->delete($idQr);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'QR Code berhasil dihapus.',
        ]);
    }
    
    /**
     * Get available periods for dropdown
     */
    private function getAvailablePeriods(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tb_periode')
            ->where('is_active', 1)
            ->orderBy('tahun', 'DESC')
            ->orderBy('bulan_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Helper: slugify string
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\p{L}\p{N}]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return substr($text, 0, 50);
    }
}
