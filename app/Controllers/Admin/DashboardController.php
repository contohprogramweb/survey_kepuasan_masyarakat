<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class DashboardController extends BaseController
{
    protected $dashboardModel;
    protected $cache;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        // Inisialisasi Cache Redis (pastikan config Cache driver = redis)
        $this->cache = \Config\Services::cache();
    }

    /**
     * Dashboard Internal IKM
     * Hanya dapat diakses oleh Admin+
     */
    public function index()
    {
        // Auth Check - pastikan hanya admin
        if (!session()->get('is_logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth/login')->with('error', 'Akses ditolak. Admin only.');
        }

        $unitId = $this->request->getGet('unit_id');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $periodeId = $this->request->getGet('periode_id');

        // Generate Cache Key unik berdasarkan filter
        $cacheKey = "dashboard_data_" . md5($unitId . '_' . $tahun . '_' . $periodeId);

        // Coba ambil dari cache (TTL 1 jam = 3600 detik)
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            // Jika tidak ada di cache, proses query
            $data = [
                'tren' => $this->dashboardModel->getTrenIkm($unitId, $tahun),
                'distribusi' => $this->dashboardModel->getDistribusiUnsur($unitId, $periodeId),
                'rekap' => $this->dashboardModel->getRekapitulasi($unitId, $tahun),
                'alerts' => $this->dashboardModel->getAlertPenurunan($tahun),
                'units' => $this->dashboardModel->getAllUnits(),
                'periodes' => $this->dashboardModel->getAllPeriodes($tahun),
                'filters' => [
                    'unit_id' => $unitId,
                    'tahun' => $tahun,
                    'periode_id' => $periodeId
                ]
            ];

            // Simpan ke cache selama 1 jam
            $this->cache->save($cacheKey, $data, 3600);
        }

        return view('admin/dashboard/index', $data);
    }
}
