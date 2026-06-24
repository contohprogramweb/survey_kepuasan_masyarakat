<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * SurveyQuestionController - Admin survey question management
 * Handles CRUD operations for survey questions (pertanyaan)
 */
class SurveyQuestionController extends BaseController
{
    protected $surveiModel;
    protected $session;

    public function __construct()
    {
        $this->surveiModel = new SurveiModel();
        $this->session = service('session');
    }

    /**
     * Display list of survey questions
     */
    public function index()
    {
        $questions = $this->surveiModel->getAllQuestions();
        
        return view('admin/survey-questions/index', [
            'questions' => $questions,
            'title' => 'Manajemen Pertanyaan Survei'
        ]);
    }

    /**
     * Get survey questions as JSON
     */
    public function data()
    {
        $questions = $this->surveiModel->getAllQuestions();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * Show create survey question form
     */
    public function new()
    {
        $elements = $this->surveiModel->getActiveElements();
        
        return view('admin/survey-questions/new', [
            'elements' => $elements,
            'title' => 'Tambah Pertanyaan Baru'
        ]);
    }

    /**
     * Create new survey question
     */
    public function create()
    {
        $rules = [
            'id_unsur'       => 'required|integer',
            'pertanyaan'     => 'required|max_length[500]',
            'tipe_input'     => 'required|in_list[rating,scale,text,textarea,multiple_choice]',
            'nilai_min'      => 'permit_empty|numeric',
            'nilai_max'      => 'permit_empty|numeric',
            'urutan'         => 'required|integer|greater_than[0]',
            'wajib_diisi'    => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_unsur'      => $this->request->getPost('id_unsur'),
            'pertanyaan'    => $this->request->getPost('pertanyaan'),
            'tipe_input'    => $this->request->getPost('tipe_input'),
            'nilai_min'     => $this->request->getPost('nilai_min'),
            'nilai_max'     => $this->request->getPost('nilai_max'),
            'urutan'        => $this->request->getPost('urutan'),
            'wajib_diisi'   => $this->request->getPost('wajib_diisi') ? 1 : 0,
            'is_active'     => 1,
        ];

        $questionId = $this->surveiModel->insertQuestion($data);

        if ($questionId) {
            return redirect()->to('/admin/survey-questions')->with('success', 'Pertanyaan berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pertanyaan.');
    }

    /**
     * Show edit survey question form
     */
    public function edit(int $id)
    {
        $question = $this->surveiModel->getQuestionById($id);

        if (!$question) {
            return redirect()->to('/admin/survey-questions')->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $elements = $this->surveiModel->getActiveElements();
        
        return view('admin/survey-questions/edit', [
            'question' => $question,
            'elements' => $elements,
            'title' => 'Edit Pertanyaan'
        ]);
    }

    /**
     * Update survey question
     */
    public function update(int $id)
    {
        $question = $this->surveiModel->getQuestionById($id);

        if (!$question) {
            return redirect()->to('/admin/survey-questions')->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $rules = [
            'id_unsur'       => 'required|integer',
            'pertanyaan'     => 'required|max_length[500]',
            'tipe_input'     => 'required|in_list[rating,scale,text,textarea,multiple_choice]',
            'nilai_min'      => 'permit_empty|numeric',
            'nilai_max'      => 'permit_empty|numeric',
            'urutan'         => 'required|integer|greater_than[0]',
            'wajib_diisi'    => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_unsur'    => $this->request->getPost('id_unsur'),
            'pertanyaan'  => $this->request->getPost('pertanyaan'),
            'tipe_input'  => $this->request->getPost('tipe_input'),
            'nilai_min'   => $this->request->getPost('nilai_min'),
            'nilai_max'   => $this->request->getPost('nilai_max'),
            'urutan'      => $this->request->getPost('urutan'),
            'wajib_diisi' => $this->request->getPost('wajib_diisi') ? 1 : 0,
        ];

        $this->surveiModel->updateQuestion($id, $data);

        return redirect()->to('/admin/survey-questions')->with('success', 'Pertanyaan berhasil diupdate.');
    }

    /**
     * Delete survey question
     */
    public function delete(int $id)
    {
        $question = $this->surveiModel->getQuestionById($id);

        if (!$question) {
            return redirect()->to('/admin/survey-questions')->with('error', 'Pertanyaan tidak ditemukan.');
        }

        // Check if question has responses
        if ($this->surveiModel->hasResponses($id)) {
            return redirect()->to('/admin/survey-questions')->with('error', 'Tidak dapat menghapus pertanyaan yang sudah memiliki respons.');
        }

        $this->surveiModel->deleteQuestion($id);

        return redirect()->to('/admin/survey-questions')->with('success', 'Pertanyaan berhasil dihapus.');
    }

    /**
     * Toggle question active status
     */
    public function toggleStatus(int $id)
    {
        $question = $this->surveiModel->getQuestionById($id);

        if (!$question) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pertanyaan tidak ditemukan.'
            ]);
        }

        $newStatus = $question['is_active'] ? 0 : 1;
        $this->surveiModel->updateQuestion($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Pertanyaan diaktifkan.' : 'Pertanyaan dinonaktifkan.',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Reorder survey questions
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
            $this->surveiModel->updateQuestion($id, ['urutan' => $index + 1]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Urutan pertanyaan berhasil diperbarui.'
        ]);
    }

    /**
     * Get questions by element
     */
    public function getByElement(int $elementId)
    {
        $questions = $this->surveiModel->getQuestionsByElement($elementId);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $questions
        ]);
    }
}
