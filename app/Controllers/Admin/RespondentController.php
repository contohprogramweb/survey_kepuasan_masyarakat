<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RespondenModel;

/**
 * RespondentController
 * 
 * Controller untuk manajemen responden (UC-10, F-10)
 * Mengelola data responden survei (opsional/anonim)
 */
class RespondentController extends BaseController
{
    protected RespondenModel $respondenModel;

    public function __construct()
    {
        $this->respondenModel = new RespondenModel();
    }

    /**
     * Display list of respondents
     */
    public function index(): string
    {
        $data = [
            'title' => 'Manajemen Responden',
            'respondents' => $this->respondenModel
                ->orderBy('tanggal_input', 'DESC')
                ->findAll(),
        ];

        return view('admin/respondents/index', $data);
    }

    /**
     * Get respondent details as JSON
     */
    public function show($id)
    {
        $respondent = $this->respondenModel->find($id);
        
        if (!$respondent) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Responden tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Anonymize sensitive data if not full access
        if (!session()->get('is_super_admin')) {
            unset($respondent['nik'], $respondent['email'], $respondent['telepon']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $respondent,
        ]);
    }

    /**
     * Delete respondent (only if no responses)
     */
    public function delete($id)
    {
        $respondent = $this->respondenModel->find($id);
        
        if (!$respondent) {
            return redirect()->to('admin/respondents')->with('error', 'Responden tidak ditemukan.');
        }

        // Check if respondent has responses
        $db = \Config\Database::connect();
        $responseCount = $db->table('tb_respons_survei')
            ->where('id_responden', $id)
            ->countAllResults();

        if ($responseCount > 0) {
            return redirect()->to('admin/respondents')
                ->with('error', 'Tidak dapat menghapus responden yang sudah memiliki respons.');
        }

        // Delete respondent
        $this->respondenModel->delete($id);

        log_message('info', 'Responden dihapus: ID ' . $id);

        return redirect()->to('admin/respondents')->with('success', 'Responden berhasil dihapus.');
    }

    /**
     * Export respondents data
     */
    public function export()
    {
        // Only super admin can export
        if (!session()->get('is_super_admin')) {
            return redirect()->to('admin/respondents')
                ->with('error', 'Akses ditolak. Hanya super admin yang dapat mengekspor data responden.');
        }

        $respondents = $this->respondenModel->findAll();

        // Generate CSV
        $csv = "ID,Nama,Usia,Jenis Kelamin,Pendidikan,Pekerjaan,Alamat,Tanggal Input\n";
        foreach ($respondents as $r) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $r['id_responden'],
                $r['nama_lengkap'] ?? '',
                $r['usia'] ?? '',
                $r['jenis_kelamin'] ?? '',
                $r['pendidikan'] ?? '',
                $r['pekerjaan'] ?? '',
                $r['alamat'] ?? '',
                $r['tanggal_input']
            );
        }

        return $this->response
            ->download('responden_' . date('Y-m-d') . '.csv', $csv);
    }
}
