<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * ConsentController - PDP Consent management
 */
class ConsentController extends BaseController
{
    protected $consentModel;
    protected $session;

    public function __construct()
    {
        $this->consentModel = new \App\Models\ConsentModel();
        $this->session = service('session');
    }

    /**
     * Display list of consents
     */
    public function index()
    {
        $consents = $this->consentModel->getRecentConsents(100);
        
        return view('admin/consents/index', [
            'consents' => $consents,
            'title' => 'Manajemen Persetujuan PDP'
        ]);
    }

    /**
     * Show consent detail
     */
    public function detail(int $id)
    {
        $consent = $this->consentModel->find($id);
        
        if (!$consent) {
            return redirect()->to('/admin/consents')->with('error', 'Data persetujuan tidak ditemukan.');
        }

        return view('admin/consents/detail', [
            'consent' => $consent,
            'title' => 'Detail Persetujuan'
        ]);
    }

    /**
     * Export consents
     */
    public function export()
    {
        // Implementasi export consents
        return redirect()->to('/admin/consents')->with('success', 'Export consents berhasil.');
    }

    /**
     * Cleanup expired consents
     */
    public function cleanupExpired()
    {
        $deleted = $this->consentModel->deleteExpiredConsents();
        
        return redirect()->to('/admin/consents')->with('success', "{$deleted} data persetujuan expired telah dibersihkan.");
    }
}
