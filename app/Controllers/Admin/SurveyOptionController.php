<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * SurveyOptionController
 * 
 * Controller untuk manajemen opsi jawaban survei
 * Mengelola pilihan jawaban untuk setiap pertanyaan
 */
class SurveyOptionController extends BaseController
{
    protected SurveiModel $optionModel;

    public function __construct()
    {
        $this->optionModel = new SurveiModel();
    }

    /**
     * Get options by question as JSON
     */
    public function byQuestion($questionId)
    {
        $db = \Config\Database::connect();
        
        $options = $db->table('tb_opsi_jawaban')
            ->where('id_pertanyaan', $questionId)
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Create new option
     */
    public function create()
    {
        $validationRules = [
            'id_pertanyaan' => 'required|integer',
            'label_opsi' => 'required|min_length[1]|max_length[100]',
            'nilai' => 'required|numeric',
            'urutan' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->table('tb_opsi_jawaban')->insert([
            'id_pertanyaan' => $this->request->getPost('id_pertanyaan'),
            'label_opsi' => $this->request->getPost('label_opsi'),
            'nilai' => $this->request->getPost('nilai'),
            'urutan' => $this->request->getPost('urutan'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_message('info', 'Opsi jawaban baru ditambahkan untuk pertanyaan ID ' . $this->request->getPost('id_pertanyaan'));

        return redirect()->back()->with('success', 'Opsi jawaban berhasil ditambahkan.');
    }

    /**
     * Update option
     */
    public function update($id)
    {
        $db = \Config\Database::connect();
        
        // Check if option exists
        $option = $db->table('tb_opsi_jawaban')
            ->where('id_opsi', $id)
            ->get()
            ->getRow();

        if (!$option) {
            return redirect()->back()->with('error', 'Opsi tidak ditemukan.');
        }

        $validationRules = [
            'label_opsi' => 'required|min_length[1]|max_length[100]',
            'nilai' => 'required|numeric',
            'urutan' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db->table('tb_opsi_jawaban')->update([
            'label_opsi' => $this->request->getPost('label_opsi'),
            'nilai' => $this->request->getPost('nilai'),
            'urutan' => $this->request->getPost('urutan'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id_opsi' => $id]);

        log_message('info', 'Opsi jawaban diperbarui: ID ' . $id);

        return redirect()->back()->with('success', 'Opsi jawaban berhasil diperbarui.');
    }

    /**
     * Delete option
     */
    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        // Check if option exists
        $option = $db->table('tb_opsi_jawaban')
            ->where('id_opsi', $id)
            ->get()
            ->getRow();

        if (!$option) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Opsi tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Check if option has been used in responses
        $usageCount = $db->table('tb_jawaban')
            ->where('id_opsi', $id)
            ->countAllResults();

        if ($usageCount > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menghapus opsi yang sudah digunakan dalam respons.'
            ]);
        }

        // Delete option
        $db->table('tb_opsi_jawaban')->where('id_opsi', $id)->delete();

        log_message('info', 'Opsi jawaban dihapus: ID ' . $id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Opsi jawaban berhasil dihapus.',
        ]);
    }

    /**
     * Reorder options
     */
    public function reorder()
    {
        $order = $this->request->getPost('order');
        
        if (!is_array($order)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data urutan tidak valid.'
            ]);
        }

        $db = \Config\Database::connect();
        foreach ($order as $index => $id) {
            $db->table('tb_opsi_jawaban')
                ->update(['urutan' => $index + 1], ['id_opsi' => $id]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Urutan opsi berhasil diperbarui.',
        ]);
    }
}
