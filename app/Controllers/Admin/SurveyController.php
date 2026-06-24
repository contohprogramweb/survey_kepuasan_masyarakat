<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * SurveyController - Admin survey management
 */
class SurveyController extends BaseController
{
    protected $surveyModel;
    protected $session;

    public function __construct()
    {
        $this->surveyModel = new \App\Models\SurveiModel();
        $this->session = service('session');
    }

    /**
     * Display list of surveys
     */
    public function index()
    {
        $surveys = $this->surveyModel->findAll();
        
        return view('admin/surveys/index', [
            'surveys' => $surveys,
            'title' => 'Manajemen Survei'
        ]);
    }

    /**
     * Show survey detail
     */
    public function show(int $id)
    {
        $survey = $this->surveyModel->find($id);
        
        if (!$survey) {
            return redirect()->to('/admin/surveys')->with('error', 'Survei tidak ditemukan.');
        }

        return view('admin/surveys/show', [
            'survey' => $survey,
            'title' => 'Detail Survei'
        ]);
    }

    /**
     * Publish survey
     */
    public function publish(int $id)
    {
        $this->surveyModel->update($id, ['is_active' => 1]);
        return redirect()->to('/admin/surveys')->with('success', 'Survei berhasil dipublish.');
    }

    /**
     * Unpublish survey
     */
    public function unpublish(int $id)
    {
        $this->surveyModel->update($id, ['is_active' => 0]);
        return redirect()->to('/admin/surveys')->with('success', 'Survei berhasil diunpublish.');
    }

    /**
     * Duplicate survey
     */
    public function duplicate(int $id)
    {
        $survey = $this->surveyModel->find($id);
        
        if (!$survey) {
            return redirect()->to('/admin/surveys')->with('error', 'Survei tidak ditemukan.');
        }

        // Duplikat data survei
        unset($survey['id_survei']);
        $survey['nama_survei'] = $survey['nama_survei'] . ' (Copy)';
        $survey['is_active'] = 0;
        
        $newId = $this->surveyModel->insert($survey);

        if ($newId) {
            return redirect()->to('/admin/surveys')->with('success', 'Survei berhasil diduplikasi.');
        }

        return redirect()->to('/admin/surveys')->with('error', 'Gagal menduplikasi survei.');
    }

    /**
     * Preview survey
     */
    public function preview(int $id)
    {
        $survey = $this->surveyModel->find($id);
        
        if (!$survey) {
            return redirect()->to('/admin/surveys')->with('error', 'Survei tidak ditemukan.');
        }

        return view('admin/surveys/preview', [
            'survey' => $survey,
            'title' => 'Preview Survei'
        ]);
    }
}
