<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * SettingsController - System settings management
 */
class SettingsController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = service('session');
    }

    /**
     * Display settings page
     */
    public function index()
    {
        // Load current settings from database or config
        $settings = $this->loadSettings();

        return view('admin/settings/index', [
            'settings' => $settings,
            'title' => 'Pengaturan Sistem'
        ]);
    }

    /**
     * Update settings
     */
    public function update()
    {
        $settings = $this->request->getPost();
        
        // Validasi dan simpan settings
        // Dalam implementasi nyata, simpan ke database
        
        log_message('info', 'Settings updated: ' . json_encode($settings));

        return redirect()->to('/admin/settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Load settings from database or config
     */
    protected function loadSettings(): array
    {
        // Default settings (dalam production load dari database)
        return [
            'app_name' => 'Sistem IKM',
            'app_version' => '2.0.0',
            'maintenance_mode' => 0,
            'allow_registration' => 0,
            'default_language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'email_from' => 'noreply@ikm.go.id',
            'email_from_name' => 'Sistem IKM',
            'survey_allow_anonymous' => 1,
            'survey_require_consent' => 1,
            'data_retention_days' => 365,
            'enable_mfa' => 1,
            'enable_oauth' => 0,
            'max_upload_size' => 2048, // KB
            'allowed_file_types' => 'jpg,png,pdf,xlsx,xls',
        ];
    }
}
