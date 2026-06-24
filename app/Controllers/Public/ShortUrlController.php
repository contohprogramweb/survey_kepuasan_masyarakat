<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\QRCodeModel;

/**
 * ShortUrlController - Public Controller untuk redirect short URL dengan tracking
 * 
 * Endpoint: GET /q/{shortCode}
 * Fungsi: Track scan, lalu redirect ke landing page survei
 */
class ShortUrlController extends BaseController
{
    protected QRCodeModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRCodeModel();
    }
    
    /**
     * Handle short URL redirect with tracking
     * GET /q/{shortCode}
     */
    public function redirect(string $shortCode)
    {
        // Find QR Code by short URL
        $qr = $this->qrModel->getByShortUrl($shortCode);
        
        if (!$qr) {
            // QR Code tidak ditemukan atau tidak aktif
            return redirect()->to('/')->with('error', 'QR Code tidak valid atau sudah tidak aktif.');
        }
        
        // Check if expired
        if (!empty($qr['expires_at']) && strtotime($qr['expires_at']) < time()) {
            return redirect()->to('/')->with('warning', 'QR Code ini sudah kadaluarsa.');
        }
        
        // Increment scan count (analytics tracking)
        $this->qrModel->incrementScanCount($qr['id_qr']);
        
        // Log scan event for detailed analytics (optional - bisa ditambahkan ke tabel audit)
        $this->logScanEvent($qr);
        
        // Extract original URL dari qr_data (tracking URL dengan UTM params)
        // qr_data berisi full tracking URL seperti: site_url('q/ABC123')?source=qr&medium=poster&campaign=periode_1
        // Kita perlu extract landing page URL sebenarnya
        $landingUrl = $this->extractLandingUrl($qr['qr_data'], $qr['id_unit'], $qr['id_periode']);
        
        // Redirect ke landing page survei
        return redirect()->to($landingUrl);
    }
    
    /**
     * Extract landing URL from tracking URL
     */
    private function extractLandingUrl(string $trackingUrl, int $idUnit, ?int $idPeriode): string
    {
        // Build clean landing URL tanpa UTM params
        // Format: /survei/index/{id_unit}/{id_periode}
        return site_url("survei/index/{$idUnit}/{$idPeriode}");
    }
    
    /**
     * Log scan event untuk analytics detail
     * Bisa disimpan ke tabel terpisah atau audit log
     */
    private function logScanEvent(array $qr): void
    {
        $db = \Config\Database::connect();
        
        $scanData = [
            'id_qr' => $qr['id_qr'],
            'scanned_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'referer' => $this->request->getServer('HTTP_REFERER'),
        ];
        
        // Insert ke tabel qr_scan_log jika ada, atau skip jika tidak
        try {
            $db->table('tb_qr_scan_log')->insert($scanData);
        } catch (\Exception $e) {
            // Tabel mungkin belum ada, log saja
            log_message('info', '[ShortUrlController] Scan logged for QR ID: ' . $qr['id_qr']);
        }
    }
    
    /**
     * Analytics endpoint - Get scan statistics (optional, admin only)
     * GET /q/stats/{shortCode}
     */
    public function stats(string $shortCode)
    {
        // Require authentication (bisa ditambahkan filter auth)
        
        $qr = $this->qrModel->getByShortUrl($shortCode);
        
        if (!$qr) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'QR Code tidak ditemukan.',
            ]);
        }
        
        // Get scan history if available
        $db = \Config\Database::connect();
        $scanHistory = [];
        
        try {
            $scanHistory = $db->table('tb_qr_scan_log')
                ->select('scanned_at, ip_address, user_agent, referer')
                ->where('id_qr', $qr['id_qr'])
                ->orderBy('scanned_at', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            // Tabel tidak ada, abaikan
        }
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => [
                'id_qr' => $qr['id_qr'],
                'short_url' => $qr['short_url'],
                'scan_count' => $qr['scan_count'],
                'last_scanned_at' => $qr['last_scanned_at'],
                'created_at' => $qr['created_at'],
            ],
            'recent_scans' => $scanHistory,
        ]);
    }
}
