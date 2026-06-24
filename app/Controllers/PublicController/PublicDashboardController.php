<?php

namespace App\Controllers\PublicController;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\UnitLayananModel;
use App\Models\PeriodeModel;

class PublicDashboardController extends BaseController
{
    protected $dashboardModel;
    protected $unitModel;
    protected $periodeModel;
    protected $cache;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->unitModel = new UnitLayananModel();
        $this->periodeModel = new PeriodeModel();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Display public dashboard (transparency)
     * Only shows published periods (is_published = 1)
     * No login required
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        // Get filter parameters
        $unitId = $this->request->getGet('unit_id');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $periodeId = $this->request->getGet('periode_id');

        // Generate Cache Key (TTL 1 jam)
        $cacheKey = "public_dashboard_" . md5($unitId . '_' . $tahun . '_' . $periodeId);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            // Get only published periods
            $publishedPeriodes = $this->periodeModel
                ->where('is_published', 1)
                ->orderBy('tahun', 'DESC')
                ->orderBy('urutan', 'DESC')
                ->findAll();

            // Get all units for filter dropdown
            $units = $this->unitModel
                ->select('id, nama_unit')
                ->orderBy('nama_unit', 'ASC')
                ->findAll();

            // Get dashboard data (only from published periods)
            $trenData = $this->dashboardModel->getPublicTrenIkm($unitId, $tahun);
            $distribusiData = $this->dashboardModel->getPublicDistribusiUnsur($unitId, $periodeId);
            $rekapData = $this->dashboardModel->getPublicRekapitulasi($unitId, $tahun);

            // Calculate summary statistics
            $summary = $this->calculateSummary($rekapData);

            $data = [
                'title' => 'Dashboard Transparansi IKM - Indeks Kepuasan Masyarakat',
                'tren' => $trenData,
                'distribusi' => $distribusiData,
                'rekap' => $rekapData,
                'summary' => $summary,
                'units' => $units,
                'periodes' => $publishedPeriodes,
                'filters' => [
                    'unit_id' => $unitId,
                    'tahun' => $tahun,
                    'periode_id' => $periodeId
                ],
                'currentLocale' => get_current_locale(),
                'supportedLocales' => ['id' => 'Indonesia', 'en' => 'English']
            ];

            // Cache for 1 hour
            $this->cache->save($cacheKey, $data, 3600);
        }

        return view('public/dashboard_ikm', $data);
    }

    /**
     * Calculate summary statistics from rekap data
     */
    protected function calculateSummary($rekapData): array
    {
        if (empty($rekapData)) {
            return [
                'total_responden' => 0,
                'nilai_ikm' => 0,
                'mutu_pelayanan' => '-',
                'total_unit' => 0
            ];
        }

        $latest = $rekapData[0];
        $totalResponden = array_sum(array_column($rekapData, 'total_responden'));
        $avgIkm = array_sum(array_column($rekapData, 'nilai_ikm')) / count($rekapData);
        
        // Determine mutu pelayanan based on latest IKM
        $mutu = $this->determineMutu($latest['nilai_ikm']);

        return [
            'total_responden' => $totalResponden,
            'nilai_ikm' => round($avgIkm, 2),
            'mutu_pelayanan' => $mutu,
            'total_unit' => count($this->unitModel->findAll())
        ];
    }

    /**
     * Determine mutu pelayanan based on IKM score
     */
    protected function determineMutu($ikm): string
    {
        if ($ikm >= 85) {
            return 'Sangat Baik';
        } elseif ($ikm >= 70) {
            return 'Baik';
        } elseif ($ikm >= 55) {
            return 'Kurang Baik';
        } else {
            return 'Tidak Baik';
        }
    }

    /**
     * Generate JSON-LD structured data for SEO
     */
    protected function generateJsonLd($data): string
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Dataset',
            'name' => 'Dashboard Transparansi IKM - Indeks Kepuasan Masyarakat',
            'description' => 'Data transparansi Indeks Kepuasan Masyarakat (IKM) untuk mengukur kualitas pelayanan publik',
            'url' => current_url(),
            'keywords' => 'IKM, Indeks Kepuasan Masyarakat, Pelayanan Publik, Survei Kepuasan',
            'publisher' => [
                '@type' => 'GovernmentOrganization',
                'name' => 'Pemerintah Daerah',
                'url' => base_url()
            ],
            'temporalCoverage' => $data['filters']['tahun'] . '/' . $data['filters']['tahun'],
            'variableMeasured' => [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Nilai IKM',
                    'unitText' => 'Skala 0-100'
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Mutu Pelayanan',
                    'unitText' => 'Kategorikal'
                ]
            ]
        ];

        return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * API endpoint for AJAX data loading
     * Returns JSON response with dashboard data
     */
    public function getData()
    {
        $unitId = $this->request->getGet('unit_id');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $periodeId = $this->request->getGet('periode_id');

        $cacheKey = "public_dashboard_api_" . md5($unitId . '_' . $tahun . '_' . $periodeId);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $trenData = $this->dashboardModel->getPublicTrenIkm($unitId, $tahun);
            $distribusiData = $this->dashboardModel->getPublicDistribusiUnsur($unitId, $periodeId);
            $rekapData = $this->dashboardModel->getPublicRekapitulasi($unitId, $tahun);
            $summary = $this->calculateSummary($rekapData);

            $data = [
                'status' => 'success',
                'data' => [
                    'tren' => $trenData,
                    'distribusi' => $distribusiData,
                    'rekap' => $rekapData,
                    'summary' => $summary
                ]
            ];

            $this->cache->save($cacheKey, $data, 3600);
        }

        return $this->response->setJSON($data);
    }
}
