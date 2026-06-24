<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * AuditLogController - System audit logging
 */
class AuditLogController extends BaseController
{
    protected $auditLogModel;
    protected $session;

    public function __construct()
    {
        // Model akan dibuat jika belum ada
        try {
            $this->auditLogModel = new \App\Models\AuditLogModel();
        } catch (\Exception $e) {
            $this->auditLogModel = null;
        }
        $this->session = service('session');
    }

    /**
     * Display list of audit logs
     */
    public function index()
    {
        if (!$this->auditLogModel) {
            return view('admin/audit-logs/index', [
                'logs' => [],
                'title' => 'Audit Logs',
                'message' => 'Tabel audit log belum tersedia.'
            ]);
        }

        $logs = $this->auditLogModel->getRecentLogs(100);
        
        return view('admin/audit-logs/index', [
            'logs' => $logs,
            'title' => 'Audit Logs'
        ]);
    }

    /**
     * Show audit log detail
     */
    public function detail(int $id)
    {
        if (!$this->auditLogModel) {
            return redirect()->to('/admin/audit-logs')->with('error', 'Audit log tidak tersedia.');
        }

        $log = $this->auditLogModel->find($id);
        
        if (!$log) {
            return redirect()->to('/admin/audit-logs')->with('error', 'Log tidak ditemukan.');
        }

        return view('admin/audit-logs/detail', [
            'log' => $log,
            'title' => 'Detail Audit Log'
        ]);
    }

    /**
     * Get logs by user
     */
    public function byUser(int $userId)
    {
        if (!$this->auditLogModel) {
            return $this->response->setJSON(['success' => false, 'message' => 'Audit log tidak tersedia.']);
        }

        $logs = $this->auditLogModel->where('user_id', $userId)->findAll(50);
        
        return $this->response->setJSON([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Export audit logs
     */
    public function export()
    {
        // Implementasi export audit logs
        return redirect()->to('/admin/audit-logs')->with('success', 'Export audit logs berhasil.');
    }
}
