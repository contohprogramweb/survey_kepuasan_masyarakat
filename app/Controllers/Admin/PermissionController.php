<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * PermissionController - Admin permission management
 */
class PermissionController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = service('session');
    }

    /**
     * Display list of permissions
     */
    public function index()
    {
        $permissions = $this->getAllPermissions();
        
        return view('admin/permissions/index', [
            'permissions' => $permissions,
            'title' => 'Manajemen Permissions'
        ]);
    }

    /**
     * Get all permissions grouped by module
     */
    protected function getAllPermissions(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'Melihat dashboard',
                'dashboard.export' => 'Export data dashboard',
            ],
            'User Management' => [
                'users.manage' => 'Kelola pengguna (semua aksi)',
                'users.create' => 'Buat pengguna baru',
                'users.edit' => 'Edit pengguna',
                'users.delete' => 'Hapus pengguna',
            ],
            'Survey Management' => [
                'surveys.manage' => 'Kelola survei (semua aksi)',
                'surveys.view' => 'Lihat survei',
                'surveys.create' => 'Buat survei baru',
                'surveys.edit' => 'Edit survei',
                'surveys.delete' => 'Hapus survei',
                'surveys.publish' => 'Publish/Unpublish survei',
            ],
            'Responses' => [
                'responses.view' => 'Lihat respons survei',
                'responses.export' => 'Export respons survei',
            ],
            'Reports' => [
                'reports.view' => 'Lihat laporan',
                'reports.generate' => 'Generate laporan',
                'reports.download' => 'Download laporan',
            ],
            'Settings' => [
                'settings.manage' => 'Kelola pengaturan sistem',
            ],
            'Audit Logs' => [
                'audit_logs.view' => 'Lihat audit logs',
                'audit_logs.export' => 'Export audit logs',
            ],
            'Backup' => [
                'backup.manage' => 'Kelola backup & restore',
            ],
        ];
    }

    /**
     * Show permission detail
     */
    public function show(int $id)
    {
        $permissions = $this->getAllPermissions();
        $allPerms = [];
        
        foreach ($permissions as $module => $perms) {
            foreach ($perms as $key => $desc) {
                $allPerms[] = ['key' => $key, 'description' => $desc];
            }
        }

        if (!isset($allPerms[$id - 1])) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tidak ditemukan.');
        }

        return view('admin/permissions/show', [
            'permission' => $allPerms[$id - 1],
            'title' => 'Detail Permission'
        ]);
    }
}
