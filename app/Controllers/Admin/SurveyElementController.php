<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * SurveyElementController - Admin survey element management
 * Handles CRUD operations for survey elements (unsur)
 */
class SurveyElementController extends BaseController
{
    protected $surveiModel;
    protected $session;

    public function __construct()
    {
        $this->surveiModel = new SurveiModel();
        $this->session = service('session');
    }

    /**
     * Display list of survey elements
     */
    public function index()
    {
        $elements = $this->surveiModel->getAllElements();
        
        return view('admin/survey-elements/index', [
            'elements' => $elements,
            'title' => 'Manajemen Unsur Survei'
        ]);
    }

    /**
     * Get survey elements as JSON
     */
    public function data()
    {
        $elements = $this->surveiModel->getAllElements();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $elements
        ]);
    }

    /**
     * Show create survey element form
     */
    public function new()
    {
        return view('admin/survey-elements/new', [
            'title' => 'Tambah Unsur Baru'
        ]);
    }

    /**
     * Create new survey element
     */
    public function create()
    {
        $rules = [
            'kode_unsur'     => 'required|alpha|max_length[10]|is_unique[tb_survei.kode_unsur]',
            'nama_unsur'     => 'required|max_length[255]',
            'deskripsi'      => 'permit_empty|max_length[1000]',
            'bobot'          => 'required|numeric|greater_than[0]',
            'urutan'         => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_unsur'    => strtoupper($this->request->getPost('kode_unsur')),
            'nama_unsur'    => $this->request->getPost('nama_unsur'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'bobot'         => $this->request->getPost('bobot'),
            'urutan'        => $this->request->getPost('urutan'),
            'is_active'     => 1,
        ];

        $elementId = $this->surveiModel->insertElement($data);

        if ($elementId) {
            return redirect()->to('/admin/survey-elements')->with('success', 'Unsur berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan unsur.');
    }

    /**
     * Show edit survey element form
     */
    public function edit(int $id)
    {
        $element = $this->surveiModel->getElementById($id);

        if (!$element) {
            return redirect()->to('/admin/survey-elements')->with('error', 'Unsur tidak ditemukan.');
        }

        return view('admin/survey-elements/edit', [
            'element' => $element,
            'title' => 'Edit Unsur'
        ]);
    }

    /**
     * Update survey element
     */
    public function update(int $id)
    {
        $element = $this->surveiModel->getElementById($id);

        if (!$element) {
            return redirect()->to('/admin/survey-elements')->with('error', 'Unsur tidak ditemukan.');
        }

        $rules = [
            'kode_unsur'     => "required|alpha|max_length[10]|is_unique[tb_survei.kode_unsur,id_survei,{$id}]",
            'nama_unsur'     => 'required|max_length[255]',
            'deskripsi'      => 'permit_empty|max_length[1000]',
            'bobot'          => 'required|numeric|greater_than[0]',
            'urutan'         => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_unsur' => strtoupper($this->request->getPost('kode_unsur')),
            'nama_unsur' => $this->request->getPost('nama_unsur'),
            'deskripsi'  => $this->request->getPost('deskripsi'),
            'bobot'      => $this->request->getPost('bobot'),
            'urutan'     => $this->request->getPost('urutan'),
        ];

        $this->surveiModel->updateElement($id, $data);

        return redirect()->to('/admin/survey-elements')->with('success', 'Unsur berhasil diupdate.');
    }

    /**
     * Delete survey element
     */
    public function delete(int $id)
    {
        $element = $this->surveiModel->getElementById($id);

        if (!$element) {
            return redirect()->to('/admin/survey-elements')->with('error', 'Unsur tidak ditemukan.');
        }

        // Check if element has questions
        if ($this->surveiModel->hasQuestions($id)) {
            return redirect()->to('/admin/survey-elements')->with('error', 'Tidak dapat menghapus unsur yang masih memiliki pertanyaan.');
        }

        $this->surveiModel->deleteElement($id);

        return redirect()->to('/admin/survey-elements')->with('success', 'Unsur berhasil dihapus.');
    }

    /**
     * Toggle element active status
     */
    public function toggleStatus(int $id)
    {
        $element = $this->surveiModel->getElementById($id);

        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unsur tidak ditemukan.'
            ]);
        }

        $newStatus = $element['is_active'] ? 0 : 1;
        $this->surveiModel->updateElement($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Unsur diaktifkan.' : 'Unsur dinonaktifkan.',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Reorder survey elements
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

        foreach ($order as $index => $id) {
            $this->surveiModel->updateElement($id, ['urutan' => $index + 1]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Urutan unsur berhasil diperbarui.'
        ]);
    }
}
