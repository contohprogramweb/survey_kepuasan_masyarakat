<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * ResponseController
 * 
 * Controller untuk manajemen respons survei (UC-08, UC-10)
 * Mengelola jawaban responden terhadap kuesioner
 */
class ResponseController extends BaseController
{
    protected SurveiModel $responseModel;

    public function __construct()
    {
        $this->responseModel = new SurveiModel();
    }

    /**
     * Display list of survey responses
     */
    public function index(): string
    {
        $db = \Config\Database::connect();
        
        // Get responses with related data
        $responses = $db->table('tb_respons_survei rs')
            ->select('rs.id_respons, rs.tanggal_input, rs.nilai_ikm, 
                     u.nama_unit, p.nama_periode, 
                     COUNT(rj.id_jawaban) as total_jawaban')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->join('tb_periode p', 'p.id_periode = rs.id_periode')
            ->leftJoin('tb_jawaban rj', 'rj.id_respons = rs.id_respons')
            ->groupBy('rs.id_respons')
            ->orderBy('rs.tanggal_input', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Manajemen Respons Survei',
            'responses' => $responses,
        ];

        return view('admin/responses/index', $data);
    }

    /**
     * Get responses by survey/period
     */
    public function bySurvey($periodId)
    {
        $db = \Config\Database::connect();
        
        $responses = $db->table('tb_respons_survei rs')
            ->select('rs.*, u.nama_unit, r.nama_responden')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->leftJoin('tb_responden r', 'r.id_responden = rs.id_responden')
            ->where('rs.id_periode', $periodId)
            ->orderBy('rs.tanggal_input', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $responses,
        ]);
    }

    /**
     * Show response details
     */
    public function show($id)
    {
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
                'message' => 'Respons tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Get response details (answers)
        $answers = $db->table('tb_jawaban j')
            ->select('j.*, k.nama_unsur, k.kode_unsur')
            ->join('tb_kuesioner k', 'k.id_kuesioner = j.id_kuesioner')
            ->where('j.id_respons', $id)
            ->orderBy('k.urutan', 'ASC')
            ->get()
            ->getResultArray();

        // Get saran if exists
        $saran = $db->table('tb_saran')
            ->where('id_respons', $id)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($response, [
                'answers' => $answers,
                'saran' => $saran,
            ]),
        ]);
    }

    /**
     * Export responses for a specific period
     */
    public function export($periodId)
    {
        $db = \Config\Database::connect();
        
        // Get all responses with answers for the period
        $responses = $db->table('tb_respons_survei rs')
            ->select('rs.id_respons, rs.tanggal_input, rs.nilai_ikm,
                     u.nama_unit, p.nama_periode')
            ->join('tb_unit_layanan u', 'u.id_unit = rs.id_unit')
            ->join('tb_periode p', 'p.id_periode = rs.id_periode')
            ->where('rs.id_periode', $periodId)
            ->orderBy('rs.tanggal_input', 'ASC')
            ->get()
            ->getResultArray();

        // Get questions for columns
        $questions = $db->table('tb_pertanyaan pq')
            ->select('pq.id_pertanyaan, pq.pertanyaan, k.kode_unsur')
            ->join('tb_kuesioner k', 'k.id_kuesioner = pq.id_kuesioner')
            ->orderBy('k.urutan', 'ASC')
            ->orderBy('pq.urutan', 'ASC')
            ->get()
            ->getResultArray();

        // Generate CSV
        $headers = ['ID Respons', 'Tanggal', 'Unit Layanan', 'Periode', 'Nilai IKM'];
        foreach ($questions as $q) {
            $headers[] = "{$q['kode_unsur']} - {$q['pertanyaan']}";
        }
        $headers[] = 'Saran/Masukan';

        $csv = implode(',', $headers) . "\n";

        foreach ($responses as $r) {
            $row = [
                $r['id_respons'],
                $r['tanggal_input'],
                $r['nama_unit'],
                $r['nama_periode'],
                number_format($r['nilai_ikm'], 2),
            ];

            // Get answers for this response
            $answers = $db->table('tb_jawaban')
                ->where('id_respons', $r['id_respons'])
                ->get()
                ->getResultArray();

            $answerMap = [];
            foreach ($answers as $a) {
                $answerMap[$a['id_pertanyaan']] = $a['nilai_jawaban'];
            }

            // Add answer values
            foreach ($questions as $q) {
                $row[] = $answerMap[$q['id_pertanyaan']] ?? '';
            }

            // Get saran
            $saran = $db->table('tb_saran')
                ->where('id_respons', $r['id_respons'])
                ->get()
                ->getRow();
            $row[] = $saran ? '"'.str_replace('"', '""', $saran->saran).'"' : '';

            $csv .= implode(',', $row) . "\n";
        }

        return $this->response
            ->download("respons_periode_{$periodId}_" . date('Y-m-d') . '.csv', $csv);
    }

    /**
     * Delete a response (admin only, with confirmation)
     */
    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        // Check if response exists
        $response = $db->table('tb_respons_survei')
            ->where('id_respons', $id)
            ->get()
            ->getRow();

        if (!$response) {
            return redirect()->to('admin/responses')->with('error', 'Respons tidak ditemukan.');
        }

        // Delete related data first (cascade)
        $db->table('tb_jawaban')->where('id_respons', $id)->delete();
        $db->table('tb_saran')->where('id_respons', $id)->delete();
        
        // Delete response
        $db->table('tb_respons_survei')->where('id_respons', $id)->delete();

        log_message('info', 'Respons survei dihapus: ID ' . $id);

        return redirect()->to('admin/responses')->with('success', 'Respons berhasil dihapus.');
    }
}
