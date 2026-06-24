<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * ResponseAPI Controller
 * 
 * RESTful API untuk manajemen respons survei
 * Endpoint: /api/responses/*
 */
class ResponseAPI extends BaseController
{
    protected SurveiModel $responseModel;
    
    public function __construct()
    {
        $this->responseModel = new SurveiModel();
    }
    
    /**
     * GET /api/responses
     * Get list responses (requires authentication)
     */
    public function index()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        
        // Get query parameters
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        $idUnit = $this->request->getVar('id_unit');
        $idPeriode = $this->request->getVar('id_periode');
        
        $builder = $db->table('tb_respons_survei rs')
            ->select('rs.*, u.nama_unit, p.nama_periode')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->join('tb_periode p', 'p.id_periode = rs.id_periode');
        
        if ($idUnit) {
            $builder->where('rs.id_unit', $idUnit);
        }
        if ($idPeriode) {
            $builder->where('rs.id_periode', $idPeriode);
        }
        
        $responses = $builder
            ->orderBy('rs.tanggal_input', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
        
        $total = $db->table('tb_respons_survei')
            ->when($idUnit, fn($b) => $b->where('id_unit', $idUnit))
            ->when($idPeriode, fn($b) => $b->where('id_periode', $idPeriode))
            ->countAllResults();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $responses,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
    }
    
    /**
     * GET /api/responses/{id}
     * Get response detail with answers
     */
    public function show($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        
        // Get response header
        $response = $db->table('tb_respons_survei rs')
            ->select('rs.*, u.nama_unit, p.nama_periode')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->join('tb_periode p', 'p.id_periode = rs.id_periode')
            ->where('rs.id_respons', $id)
            ->get()
            ->getRowArray();
        
        if (!$response) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Response not found'
            ])->setStatusCode(404);
        }
        
        // Get answers
        $answers = $db->table('tb_jawaban j')
            ->select('j.*, pq.pertanyaan, k.nama_unsur, k.kode_unsur')
            ->join('tb_pertanyaan pq', 'pq.id_pertanyaan = j.id_pertanyaan')
            ->join('tb_kuesioner k', 'k.id_kuesioner = pq.id_kuesioner')
            ->where('j.id_respons', $id)
            ->orderBy('k.urutan', 'ASC')
            ->orderBy('pq.urutan', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get saran
        $saran = $db->table('tb_saran')
            ->where('id_respons', $id)
            ->get()
            ->getRowArray();
        
        $response['answers'] = $answers;
        $response['saran'] = $saran;
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $response
        ]);
    }
    
    /**
     * GET /api/responses/stats
     * Get response statistics
     */
    public function stats()
    {
        $db = \Config\Database::connect();
        
        $idUnit = $this->request->getVar('id_unit');
        $idPeriode = $this->request->getVar('id_periode');
        
        $where = [];
        if ($idUnit) $where['id_unit'] = $idUnit;
        if ($idPeriode) $where['id_periode'] = $idPeriode;
        
        // Total responses
        $total = $db->table('tb_respons_survei')
            ->where($where)
            ->countAllResults();
        
        // Average IKM
        $avgIKM = $db->table('tb_respons_survei')
            ->select('AVG(nilai_ikm) as avg_ikm, MIN(nilai_ikm) as min_ikm, MAX(nilai_ikm) as max_ikm')
            ->where($where)
            ->get()
            ->getRowArray();
        
        // Responses by category
        $byCategory = $db->table('tb_respons_survei')
            ->select("
                SUM(CASE WHEN nilai_ikm >= 3.51 THEN 1 ELSE 0 END) as sangat_baik,
                SUM(CASE WHEN nilai_ikm BETWEEN 2.51 AND 3.50 THEN 1 ELSE 0 END) as baik,
                SUM(CASE WHEN nilai_ikm BETWEEN 1.51 AND 2.50 THEN 1 ELSE 0 END) as kurang_baik,
                SUM(CASE WHEN nilai_ikm < 1.51 THEN 1 ELSE 0 END) as sangat_kurang
            ")
            ->where($where)
            ->get()
            ->getRowArray();
        
        // Responses trend (last 7 days)
        $trend = $db->table('tb_respons_survei')
            ->select("DATE(tanggal_input) as tanggal, COUNT(*) as total")
            ->where('tanggal_input >=', date('Y-m-d', strtotime('-7 days')))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'total' => $total,
                'average_ikm' => (float)($avgIKM['avg_ikm'] ?? 0),
                'min_ikm' => (float)($avgIKM['min_ikm'] ?? 0),
                'max_ikm' => (float)($avgIKM['max_ikm'] ?? 0),
                'by_category' => $byCategory,
                'trend' => $trend
            ]
        ]);
    }
    
    /**
     * GET /api/responses/export
     * Export responses as CSV (requires authentication)
     */
    public function export()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $db = \Config\Database::connect();
        $idPeriode = $this->request->getVar('id_periode');
        
        $where = [];
        if ($idPeriode) $where['id_periode'] = $idPeriode;
        
        $responses = $db->table('tb_respons_survei rs')
            ->select('rs.id_respons, rs.tanggal_input, rs.nilai_ikm, u.nama_unit, p.nama_periode')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->join('tb_periode p', 'p.id_periode = rs.id_periode')
            ->where($where)
            ->orderBy('rs.tanggal_input', 'ASC')
            ->get()
            ->getResultArray();
        
        // Generate CSV
        $headers = ['ID', 'Tanggal', 'Unit', 'Periode', 'Nilai IKM', 'Kategori'];
        $csv = implode(',', $headers) . "\n";
        
        foreach ($responses as $r) {
            $kategori = '';
            if ($r['nilai_ikm'] >= 3.51) $kategori = 'Sangat Baik';
            elseif ($r['nilai_ikm'] >= 2.51) $kategori = 'Baik';
            elseif ($r['nilai_ikm'] >= 1.51) $kategori = 'Kurang Baik';
            else $kategori = 'Sangat Kurang';
            
            $row = [
                $r['id_respons'],
                $r['tanggal_input'],
                '"'.str_replace('"', '""', $r['nama_unit']).'"',
                '"'.str_replace('"', '""', $r['nama_periode']).'"',
                number_format($r['nilai_ikm'], 2),
                $kategori
            ];
            $csv .= implode(',', $row) . "\n";
        }
        
        return $this->response
            ->setContentType('text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="responses_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}
