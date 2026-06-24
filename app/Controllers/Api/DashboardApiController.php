<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use CodeIgniter\API\ResponseTrait;

class DashboardApiController extends BaseController
{
    use ResponseTrait;
    
    protected $dashboardModel;
    protected $cache;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get dashboard data via API
     * Endpoint: /api/dashboard/data
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getData()
    {
        // Auth check untuk API
        if (!session()->get('is_logged_in') || session()->get('role') !== 'admin') {
            return $this->failUnauthorized('Unauthorized access. Admin only.');
        }

        $unitId = $this->request->getGet('unit_id');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $periodeId = $this->request->getGet('periode_id');

        $cacheKey = "api_dashboard_" . md5($unitId . '_' . $tahun . '_' . $periodeId);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data = [
                'status' => 'success',
                'data' => [
                    'tren' => $this->dashboardModel->getTrenIkm($unitId, $tahun),
                    'distribusi' => $this->dashboardModel->getDistribusiUnsur($unitId, $periodeId),
                    'rekap' => $this->dashboardModel->getRekapitulasi($unitId, $tahun),
                    'alerts' => $this->dashboardModel->getAlertPenurunan($tahun),
                ]
            ];
            $this->cache->save($cacheKey, $data, 3600);
        }

        return $this->respond($data);
    }
}
