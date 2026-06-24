<?php

namespace App\Controller;

use App\Model\KuesionerModel;
use PDO;

class KuesionerController
{
    private KuesionerModel $model;
    private PDO $db;

    public function __construct(KuesionerModel $model, PDO $db)
    {
        $this->model = $model;
        $this->db = $db;
    }

    /**
     * Display list of kuesioner (Admin view)
     */
    public function index(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        
        // Initialize 9 unsur wajib jika belum ada
        $this->model->initializeUnsurWajib();
        
        require __DIR__ . '/../../views/kuesioner/index.php';
    }

    /**
     * DataTables Server-Side Processing Endpoint
     */
    public function data(): void
    {
        header('Content-Type: application/json');
        $this->checkPermission(['Super Admin', 'Admin']);
        
        $result = $this->model->getDatatable($_GET);
        echo json_encode($result);
        exit;
    }

    /**
     * Show edit form for kuesioner
     */
    public function edit(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        
        $kuesioner = $this->model->findById($id);
        if (!$kuesioner) {
            http_response_code(404);
            die("Kuesioner tidak ditemukan.");
        }
        
        $isUnsurWajib = $this->model->isUnsurWajib($kuesioner['unsur_code']);
        $errors = [];
        
        require __DIR__ . '/../../views/kuesioner/edit.php';
    }

    /**
     * Update kuesioner
     */
    public function update(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /kuesioner/{$id}/edit");
            exit;
        }

        $kuesioner = $this->model->findById($id);
        if (!$kuesioner) {
            http_response_code(404);
            die("Kuesioner tidak ditemukan.");
        }

        $data = [
            'nama_unsur' => trim($_POST['nama_unsur'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'bobot' => floatval($_POST['bobot'] ?? 1.00),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'urutan' => intval($_POST['urutan'] ?? 0)
        ];

        $errors = [];
        if (empty($data['nama_unsur'])) {
            $errors[] = "Nama unsur/pertanyaan wajib diisi.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = $errors;
            header("Location: /kuesioner/{$id}/edit");
            exit;
        }

        try {
            $this->model->update($id, $data);
            $_SESSION['flash_success'] = "Kuesioner berhasil diperbarui.";
            header("Location: /kuesioner/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = ["Gagal memperbarui: " . $e->getMessage()];
            header("Location: /kuesioner/{$id}/edit");
        }
        exit;
    }

    /**
     * Show detail view
     */
    public function show(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin', 'Operator']);
        
        $kuesioner = $this->model->findById($id);
        if (!$kuesioner) {
            http_response_code(404);
            die("Kuesioner tidak ditemukan.");
        }
        
        $isUnsurWajib = $this->model->isUnsurWajib($kuesioner['unsur_code']);
        
        require __DIR__ . '/../../views/kuesioner/show.php';
    }

    /**
     * Toggle active/inactive status (AJAX endpoint)
     */
    public function toggleStatus(int $id): void
    {
        header('Content-Type: application/json');
        $this->checkPermission(['Super Admin', 'Admin']);
        
        try {
            $this->model->toggleStatus($id);
            echo json_encode(['success' => true, 'message' => 'Status berhasil diubah.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete kuesioner (AJAX endpoint)
     */
    public function destroy(int $id): void
    {
        header('Content-Type: application/json');
        $this->checkPermission(['Super Admin', 'Admin']);
        
        try {
            $this->model->delete($id);
            echo json_encode(['success' => true, 'message' => 'Kuesioner berhasil dihapus.']);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Preview kuesioner dari sudut pandang responden (F-19)
     * Menggunakan template yang sama dengan halaman survei publik (UC-06)
     */
    public function preview(): void
    {
        $this->checkPermission(['Super Admin', 'Admin', 'Operator']);
        
        // Get all active kuesioner for preview
        $kuesionerList = $this->model->getAllForPreview();
        
        // Sample unit info for preview
        $unitInfo = [
            'nama_unit' => 'Unit Layanan Contoh',
            'logo_path' => null,
            'alamat' => 'Jl. Contoh No. 123',
            'telepon' => '(021) 1234567',
            'email' => 'info@contoh.go.id'
        ];
        
        require __DIR__ . '/../../views/kuesioner/preview.php';
    }

    /**
     * Reorder kuesioner (AJAX endpoint)
     */
    public function reorder(): void
    {
        header('Content-Type: application/json');
        $this->checkPermission(['Super Admin', 'Admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $items = $_POST['items'] ?? [];
        
        if (empty($items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No items provided']);
            exit;
        }
        
        try {
            $this->model->updateUrutan($items);
            echo json_encode(['success' => true, 'message' => 'Urutan berhasil diperbarui.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Helper: Check RBAC permissions
     */
    private function checkPermission(array $allowedRoles): void
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $allowedRoles)) {
            http_response_code(403);
            die("Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.");
        }
    }
}
