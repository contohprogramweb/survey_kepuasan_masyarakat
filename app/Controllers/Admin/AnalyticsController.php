<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * AnalyticsController - Admin analytics and reporting
 */
class AnalyticsController extends BaseController
{
    protected $dashboardModel;
    protected $session;

    public function __construct()
    {
        $this->dashboardModel = new \App\Models\DashboardModel();
        $this->session = service('session');
    }

    /**
     * Dashboard analytics overview
     */
    public function dashboard()
    {
        $data = $this->dashboardModel->getDashboardData();
        
        return view('admin/analytics/dashboard', [
            'data' => $data,
            'title' => 'Analytics Dashboard'
        ]);
    }

    /**
     * Survey-specific analytics
     */
    public function survey(int $id)
    {
        // Load analytics for specific survey
        $analytics = $this->dashboardModel->getSurveyAnalytics($id);
        
        return view('admin/analytics/survey', [
            'surveyId' => $id,
            'analytics' => $analytics,
            'title' => 'Analytics Survei'
        ]);
    }

    /**
     * IKM Score overview
     */
    public function ikmScore()
    {
        $scores = $this->dashboardModel->getIKMScores();
        
        return view('admin/analytics/ikm_score', [
            'scores' => $scores,
            'title' => 'Skor IKM'
        ]);
    }

    /**
     * Export analytics data
     */
    public function export()
    {
        $format = $this->request->getGet('format') ?? 'excel';
        
        // Generate export file
        if ($format === 'pdf') {
            return $this->exportPdf();
        } else {
            return $this->exportExcel();
        }
    }

    /**
     * Export to PDF
     */
    protected function exportPdf()
    {
        // Implementasi export PDF
        return redirect()->to('/admin/analytics/dashboard')->with('error', 'Export PDF belum diimplementasi.');
    }

    /**
     * Export to Excel
     */
    protected function exportExcel()
    {
        // Implementasi export Excel
        return redirect()->to('/admin/analytics/dashboard')->with('error', 'Export Excel belum diimplementasi.');
    }
}
