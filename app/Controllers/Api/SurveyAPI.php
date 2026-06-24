<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * SurveyAPI Controller
 * 
 * RESTful API untuk operasi survei publik
 * Endpoint: /api/survey/*
 */
class SurveyAPI extends BaseController
{
    protected SurveiModel $surveiModel;
    
    public function __construct()
    {
        $this->surveiModel = new SurveiModel();
    }
    
    /**
     * GET /api/survey/active-period
     * Get periode survei yang sedang aktif
     */
    public function activePeriod()
    {
        $periode = $this->surveiModel->db
            ->table('tb_periode')
            ->where('status', 'aktif')
            ->get()
            ->getRowArray();
        
        if (!$periode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada periode survei aktif saat ini.'
            ])->setStatusCode(404);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $periode
        ]);
    }
    
    /**
     * GET /api/survey/elements
     * Get semua unsur kuesioner aktif dengan pertanyaan
     */
    public function elements()
    {
        $elements = $this->surveiModel->getActiveElements();
        
        foreach ($elements as &$element) {
            $questions = $this->surveiModel->getQuestionsByElement($element['id_kuesioner']);
            $element['questions'] = $questions;
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $elements
        ]);
    }
    
    /**
     * POST /api/survey/submit
     * Submit respons survei baru
     * 
     * Expected JSON:
     * {
     *   "id_unit": 1,
     *   "respondent": {...}, // optional
     *   "answers": [
     *     {"id_pertanyaan": 1, "nilai": 4},
     *     ...
     *   ],
     *   "saran": "..." // optional
     * }
     */
    public function submit()
    {
        // Validate JSON input
        $json = $this->request->getJSON(true);
        
        if (!$json) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid JSON format.'
            ])->setStatusCode(400);
        }
        
        // Check active period
        $activePeriod = $this->surveiModel->db
            ->table('tb_periode')
            ->where('status', 'aktif')
            ->get()
            ->getRowArray();
        
        if (!$activePeriod) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Periode survei tidak aktif.'
            ])->setStatusCode(403);
        }
        
        // Validate required fields
        if (empty($json['id_unit']) || empty($json['answers']) || !is_array($json['answers'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap. Diperlukan id_unit dan answers.'
            ])->setStatusCode(400);
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Insert respondent (optional)
            $idResponden = null;
            if (!empty($json['respondent'])) {
                $r = $json['respondent'];
                $db->table('tb_responden')->insert([
                    'nama_lengkap' => $r['nama_lengkap'] ?? null,
                    'nik' => $r['nik'] ?? null,
                    'usia' => $r['usia'] ?? null,
                    'jenis_kelamin' => $r['jenis_kelamin'] ?? null,
                    'pendidikan' => $r['pendidikan'] ?? null,
                    'pekerjaan' => $r['pekerjaan'] ?? null,
                    'alamat' => $r['alamat'] ?? null,
                    'email' => $r['email'] ?? null,
                    'telepon' => $r['telepon'] ?? null,
                    'tanggal_input' => date('Y-m-d H:i:s'),
                ]);
                $idResponden = $db->insertID();
            }
            
            // Calculate IKM score
            $totalNilai = 0;
            $countJawaban = 0;
            
            foreach ($json['answers'] as $answer) {
                $totalNilai += (float)($answer['nilai'] ?? 0);
                $countJawaban++;
            }
            
            $nilaiIKM = $countJawaban > 0 ? ($totalNilai / $countJawaban) : 0;
            
            // Insert response
            $db->table('tb_respons_survei')->insert([
                'id_responden' => $idResponden,
                'id_unit' => $json['id_unit'],
                'id_periode' => $activePeriod['id_periode'],
                'nilai_ikm' => $nilaiIKM,
                'tanggal_input' => date('Y-m-d H:i:s'),
            ]);
            $idRespons = $db->insertID();
            
            // Insert answers
            foreach ($json['answers'] as $answer) {
                $db->table('tb_jawaban')->insert([
                    'id_respons' => $idRespons,
                    'id_pertanyaan' => $answer['id_pertanyaan'],
                    'id_kuesioner' => $answer['id_kuesioner'] ?? null,
                    'nilai_jawaban' => $answer['nilai'],
                    'tanggal_input' => date('Y-m-d H:i:s'),
                ]);
            }
            
            // Insert saran (optional)
            if (!empty($json['saran'])) {
                $db->table('tb_saran')->insert([
                    'id_respons' => $idRespons,
                    'saran' => $json['saran'],
                    'tanggal_input' => date('Y-m-d H:i:s'),
                ]);
            }
            
            $db->transCommit();
            
            // Queue notification (async)
            // $this->notifyNewResponse($idRespons);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Respons survei berhasil disimpan.',
                'data' => [
                    'id_respons' => $idRespons,
                    'nilai_ikm' => $nilaiIKM
                ]
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'API Survey Submit Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * GET /api/survey/stats/{id_unit}
     * Get statistik survei untuk unit tertentu
     */
    public function stats($idUnit = null)
    {
        $db = \Config\Database::connect();
        
        $where = [];
        if ($idUnit) {
            $where['rs.id_unit'] = $idUnit;
        }
        
        // Total responses
        $total = $db->table('tb_respons_survei rs')
            ->where($where)
            ->countAllResults();
        
        // Average IKM
        $avgIKM = $db->table('tb_respons_survei rs')
            ->select('AVG(rs.nilai_ikm) as avg_ikm')
            ->where($where)
            ->get()
            ->getRow();
        
        // Responses by month (last 6 months)
        $byMonth = $db->table('tb_respons_survei rs')
            ->select("DATE_FORMAT(rs.tanggal_input, '%Y-%m') as bulan, COUNT(*) as total")
            ->where($where)
            ->groupBy('bulan')
            ->orderBy('bulan', 'DESC')
            ->limit(6)
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'total_responses' => $total,
                'average_ikm' => $avgIKM->avg_ikm ?? 0,
                'by_month' => $byMonth
            ]
        ]);
    }
    
    /**
     * GET /api/survey/units
     * Get list unit layanan aktif
     */
    public function units()
    {
        $units = $this->surveiModel->db
            ->table('tb_unit_layanan u')
            ->join('tb_instansi i', 'i.id_instansi = u.id_instansi')
            ->where('u.status', 'aktif')
            ->select('u.id_unit, u.nama_unit, u.kode_unit, i.nama_instansi')
            ->orderBy('u.nama_unit', 'ASC')
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $units
        ]);
    }
}
