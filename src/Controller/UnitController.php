<?php

namespace App\Controller;

use App\Model\UnitModel;
use App\Service\AuditLogService;

class UnitController
{
    private UnitModel $model;
    private AuditLogService $auditLog;

    public function __construct(UnitModel $model, AuditLogService $auditLog)
    {
        $this->model = $model;
        $this->auditLog = $auditLog;
    }

    /**
     * Display list view
     */
    public function index(): void
    {
        // Permission check: Super Admin, Admin
        $this->checkPermission(['Super Admin', 'Admin']);
        
        require __DIR__ . '/../../views/unit/index.php';
    }

    /**
     * DataTables Server-Side Processing Endpoint
     */
    public function data(): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['Super Admin', 'Admin']);

        $request = $_GET;
        $result = $this->model->getDatatable($request);
        
        echo json_encode($result);
        exit;
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        $errors = [];
        $oldInput = [];
        
        require __DIR__ . '/../../views/unit/create.php';
    }

    /**
     * Store new unit
     */
    public function store(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /units/create');
            exit;
        }

        $data = [
            'kode_unit' => trim($_POST['kode_unit'] ?? ''),
            'nama_unit' => trim($_POST['nama_unit'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'kontak' => trim($_POST['kontak'] ?? ''),
            'status' => $_POST['status'] ?? 'aktif'
        ];

        // Validasi
        $errors = [];
        if (empty($data['kode_unit'])) $errors[] = "Kode Unit wajib diisi.";
        if (empty($data['nama_unit'])) $errors[] = "Nama Unit wajib diisi.";
        
        if (!$this->model->isNamaUnitUnique($data['nama_unit'])) {
            $errors[] = "Nama Unit sudah digunakan.";
        }

        if (!empty($errors)) {
            // Kembalikan ke form dengan error (dalam real app gunakan session flash)
            $_SESSION['flash_error'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: /units/create');
            exit;
        }

        try {
            $newId = $this->model->create($data);
            $this->auditLog->log('UNIT_CREATE', "Membuat unit baru: {$data['nama_unit']} (ID: {$newId})");
            
            $_SESSION['flash_success'] = "Unit berhasil ditambahkan.";
            header('Location: /units');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = ["Gagal menyimpan: " . $e->getMessage()];
            header('Location: /units/create');
        }
        exit;
    }

    /**
     * Show detail view
     */
    public function show(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin', 'Operator']);
        
        $unit = $this->model->findById($id);
        if (!$unit) {
            http_response_code(404);
            die("Unit tidak ditemukan.");
        }

        $hasRelations = $this->model->hasRelations($id);
        
        // Ambil audit log terkait unit ini (opsional, join berdasarkan context)
        $auditLogs = []; // Implementasi sesuai kebutuhan audit log service

        require __DIR__ . '/../../views/unit/show.php';
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        
        $unit = $this->model->findById($id);
        if (!$unit) {
            http_response_code(404);
            die("Unit tidak ditemukan.");
        }

        $errors = [];
        require __DIR__ . '/../../views/unit/edit.php';
    }

    /**
     * Update unit
     */
    public function update(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /units/{$id}/edit");
            exit;
        }

        $unit = $this->model->findById($id);
        if (!$unit) {
            http_response_code(404);
            die("Unit tidak ditemukan.");
        }

        $data = [
            'kode_unit' => trim($_POST['kode_unit'] ?? ''),
            'nama_unit' => trim($_POST['nama_unit'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'kontak' => trim($_POST['kontak'] ?? ''),
            'status' => $_POST['status'] ?? 'aktif'
        ];

        $errors = [];
        if (empty($data['kode_unit'])) $errors[] = "Kode Unit wajib diisi.";
        if (empty($data['nama_unit'])) $errors[] = "Nama Unit wajib diisi.";
        
        // Cek unik nama unit (kecuali diri sendiri)
        if (!$this->model->isNamaUnitUnique($data['nama_unit'], $id)) {
            $errors[] = "Nama Unit sudah digunakan oleh unit lain.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = $errors;
            header("Location: /units/{$id}/edit");
            exit;
        }

        try {
            $this->model->update($id, $data);
            $this->auditLog->log('UNIT_UPDATE', "Memperbarui unit: {$data['nama_unit']} (ID: {$id})");
            
            $_SESSION['flash_success'] = "Unit berhasil diperbarui.";
            header("Location: /units/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = ["Gagal memperbarui: " . $e->getMessage()];
            header("Location: /units/{$id}/edit");
        }
        exit;
    }

    /**
     * Delete unit (Soft Delete)
     */
    public function destroy(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);

        $unit = $this->model->findById($id);
        if (!$unit) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Unit tidak ditemukan']);
            exit;
        }

        // Cek relasi
        if ($this->model->hasRelations($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Unit tidak dapat dihapus karena memiliki relasi data (periode).'
            ]);
            exit;
        }

        try {
            $this->model->delete($id);
            $this->auditLog->log('UNIT_DELETE', "Menghapus unit (soft): {$unit['nama_unit']} (ID: {$id})");
            
            echo json_encode(['success' => true, 'message' => 'Unit berhasil dihapus']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus unit']);
        }
        exit;
    }

    /**
     * Helper: Check RBAC
     */
    private function checkPermission(array $allowedRoles): void
    {
        // Implementasi sederhana, sesuaikan dengan sistem Auth Anda
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userRole = $_SESSION['user']['role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            die("Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.");
        }
    }
}
