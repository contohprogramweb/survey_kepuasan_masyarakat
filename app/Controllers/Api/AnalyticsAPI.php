<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * AnalyticsAPI Controller
 * 
 * RESTful API untuk analytics dan reporting
 * Endpoint: /api/analytics/*
 */
class AnalyticsAPI extends BaseController
{
    protected SurveiModel $surveiModel;
    
    public function __construct()
    {
        $this->surveiModel = new SurveiModel();
    }
    
    /**
     * GET /api/analytics/dashboard
     * Get dashboard analytics summary
     */
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        $idUnit = session()->get('id_unit');
        $isSuperAdmin = session()->get('is_super_admin');
        
        // Total responses (all time)
        $totalResponses = $db->table('tb_respons_survei')
            ->when(!$isSuperAdmin && $idUnit, fn($b) => $b->where('id_unit', $idUnit))
            ->countAllResults();
        
        // Responses this month
        $thisMonth = $db->table('tb_respons_survei')
            ->where('MONTH(tanggal_input)', date('n'))
            ->where('YEAR(tanggal_input)', date('Y'))
            ->when(!$isSuperAdmin && $idUnit, fn($b) => $b->where('id_unit', $idUnit))
            ->countAllResults();
        
        // Average IKM
        $avgIKM = $db->table('tb_respons_survei')
            ->select('AVG(nilai_ikm) as avg')
            ->when(!$isSuperAdmin && $idUnit, fn($b) => $b->where('id_unit', $idUnit))
            ->get()
            ->getRow();
        
        // IKM by unit (for super admin)
        $ikmByUnit = [];
        if ($isSuperAdmin) {
            $ikmByUnit = $db->table('tb_respons_survei rs')
                ->select('u.nama_unit, AVG(rs.nilai_ikm) as avg_ikm, COUNT(*) as total')
                ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
                ->groupBy('rs.id_unit, u.nama_unit')
                ->orderBy('avg_ikm', 'DESC')
                ->get()
                ->getResultArray();
        }
        
        // Responses trend (last 12 months)
        $trend = $db->table('tb_respons_survei')
            ->select("DATE_FORMAT(tanggal_input, '%Y-%m') as bulan, COUNT(*) as total")
            ->where('tanggal_input >=', date('Y-m-d', strtotime('-12 months')))
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC')
            ->get()
            ->getResultArray();
        
        // Satisfaction distribution
        $distribution = $db->table('tb_respons_survei')
            ->select("
                SUM(CASE WHEN nilai_ikm >= 3.51 THEN 1 ELSE 0 END) as sangat_baik,
                SUM(CASE WHEN nilai_ikm BETWEEN 2.51 AND 3.50 THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN nilai_ikm BETWEEN 1.51 AND 2.50 THEN 1 ELSE 0 END) as kurang_baik,
                SUM(CASE WHEN nilai_ikm < 1.51 THEN 1 ELSE 0 END) as sangat_kurang
            ")
            ->when(!$isSuperAdmin && $idUnit, fn($b) => $b->where('id_unit', $idUnit))
            ->get()
            ->getRowArray();
        
        // Top performing elements
        $elementScores = $db->table('tb_jawaban j')
            ->select('k.nama_unsur, AVG(j.nilai_jawaban) as avg_score, COUNT(*) as total')
            ->join('tb_pertanyaan pq', 'pq.id_pertanyaan = j.id_pertanyaan')
            ->join('tb_kuesioner k', 'k.id_kuesioner = pq.id_kuesioner')
            ->groupBy('k.id_kuesioner, k.nama_unsur')
            ->orderBy('avg_score', 'DESC')
            ->limit(9)
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_responses' => $totalResponses,
                    'responses_this_month' => $thisMonth,
                    'average_ikm' => (float)($avgIKM->avg ?? 0),
                    'ikm_category' => $this->getIKMCategory($avgIKM->avg ?? 0)
                ],
                'ikm_by_unit' => $ikmByUnit,
                'trend' => $trend,
                'satisfaction_distribution' => $distribution,
                'element_scores' => $elementScores,
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    /**
     * GET /api/analytics/element/{id_element}
     * Get detailed analytics for specific element
     */
    public function elementDetail($idElement)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        
        // Get element info
        $element = $db->table('tb_kuesioner')
            ->where('id_kuesioner', $idElement)
            ->get()
            ->getRowArray();
        
        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Element not found'
            ])->setStatusCode(404);
        }
        
        // Get questions for this element
        $questions = $db->table('tb_pertanyaan pq')
            ->select('pq.*, COUNT(j.id_jawaban) as total_jawaban, AVG(j.nilai_jawaban) as avg_score')
            ->join('tb_jawaban j', 'j.id_pertanyaan = pq.id_pertanyaan', 'left')
            ->where('pq.id_kuesioner', $idElement)
            ->groupBy('pq.id_pertanyaan')
            ->get()
            ->getResultArray();
        
        // Score distribution for this element
        $distribution = $db->table('tb_jawaban j')
            ->select('j.nilai_jawaban, COUNT(*) as count')
            ->join('tb_pertanyaan pq', 'pq.id_pertanyaan = j.id_pertanyaan')
            ->where('pq.id_kuesioner', $idElement)
            ->groupBy('j.nilai_jawaban')
            ->orderBy('j.nilai_jawaban', 'ASC')
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'element' => $element,
                'questions' => $questions,
                'score_distribution' => $distribution
            ]
        ]);
    }
    
    /**
     * GET /api/analytics/comparison
     * Compare IKM between periods or units
     */
    public function comparison()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        $type = $this->request->getVar('type') ?? 'period'; // period or unit
        
        if ($type === 'period') {
            // Compare by period
            $comparison = $db->table('tb_respons_survei rs')
                ->select('p.nama_periode, AVG(rs.nilai_ikm) as avg_ikm, COUNT(*) as total')
                ->join('tb_periode p', 'p.id_periode = rs.id_periode')
                ->groupBy('rs.id_periode, p.nama_periode')
                ->orderBy('p.tanggal_mulai', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();
        } else {
            // Compare by unit
            $comparison = $db->table('tb_respons_survei rs')
                ->select('u.nama_unit, AVG(rs.nilai_ikm) as avg_ikm, COUNT(*) as total')
                ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
                ->groupBy('rs.id_unit, u.nama_unit')
                ->orderBy('avg_ikm', 'DESC')
                ->get()
                ->getResultArray();
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $comparison
        ]);
    }
    
    /**
     * Helper: Get IKM category
     */
    private function getIKMCategory($nilai)
    {
        if ($nilai >= 3.51) return 'Sangat Baik';
        if ($nilai >= 2.51) return 'Baik';
        if ($nilai >= 1.51) return 'Kurang Baik';
        return 'Sangat Kurang';
    }
}
