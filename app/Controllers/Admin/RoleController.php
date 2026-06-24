<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * RoleController - Admin role and permission management
 */
class RoleController extends BaseController
{
    protected $session;
    
    // Roles hardcoded (dalam production bisa dari database)
    protected $roles = [
        'super_admin' => [
            'name' => 'Super Administrator',
            'description' => 'Akses penuh ke semua fitur sistem',
            'permissions' => ['*']
        ],
        'admin_unit' => [
            'name' => 'Administrator Unit',
            'description' => 'Mengelola unit layanan sendiri',
            'permissions' => ['dashboard.view', 'users.manage', 'surveys.manage', 'reports.view', 'reports.generate']
        ],
        'operator' => [
            'name' => 'Operator',
            'description' => 'Input data dan generate laporan',
            'permissions' => ['dashboard.view', 'surveys.view', 'reports.view', 'reports.generate']
        ],
        'viewer' => [
            'name' => 'Viewer',
            'description' => 'Hanya bisa melihat dashboard dan laporan',
            'permissions' => ['dashboard.view', 'reports.view']
        ]
    ];

    public function __construct()
    {
        $this->session = service('session');
    }

    /**
     * Display list of roles
     */
    public function index()
    {
        return view('admin/roles/index', [
            'roles' => $this->roles,
            'title' => 'Manajemen Role'
        ]);
    }

    /**
     * Show role detail
     */
    public function show(int $id)
    {
        // Mapping ID ke role name (untuk demo)
        $roleNames = array_keys($this->roles);
        if (!isset($roleNames[$id - 1])) {
            return redirect()->to('/admin/roles')->with('error', 'Role tidak ditemukan.');
        }

        $roleName = $roleNames[$id - 1];
        $role = $this->roles[$roleName];

        return view('admin/roles/show', [
            'roleName' => $roleName,
            'role' => $role,
            'title' => 'Detail Role'
        ]);
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(int $id)
    {
        $permissions = $this->request->getPost('permissions') ?? [];

        // Dalam implementasi nyata, simpan ke database
        log_message('info', "Assign permissions to role {$id}: " . implode(', ', $permissions));

        return redirect()->to('/admin/roles')->with('success', 'Permissions berhasil diassign.');
    }

    /**
     * Get all permissions as JSON
     */
    public function getPermissions()
    {
        $allPermissions = [
            'dashboard.view',
            'dashboard.export',
            'users.manage',
            'users.create',
            'users.edit',
            'users.delete',
            'surveys.manage',
            'surveys.view',
            'surveys.create',
            'surveys.edit',
            'surveys.delete',
            'responses.view',
            'responses.export',
            'reports.view',
            'reports.generate',
            'settings.manage',
            'audit_logs.view',
            'backup.manage'
        ];

        return $this->response->setJSON([
            'success' => true,
            'permissions' => $allPermissions
        ]);
    }
}
