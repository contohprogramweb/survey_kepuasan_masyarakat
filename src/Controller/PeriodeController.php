<?php

namespace App\Controller;

use App\Model\PeriodeModel;
use App\Service\AuditLogService;
use App\Service\PeriodeStatusScheduler;
use PDO;

class PeriodeController
{
    private PeriodeModel $model;
    private AuditLogService $auditLog;
    private PDO $db;

    public function __construct(PeriodeModel $model, AuditLogService $auditLog, PDO $db)
    {
        $this->model = $model;
        $this->auditLog = $auditLog;
        $this->db = $db;
    }

    public function index(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        require __DIR__ . '/../../views/periode/index.php';
    }

    public function data(): void
    {
        header('Content-Type: application/json');
        $this->checkPermission(['Super Admin', 'Admin']);
        echo json_encode($this->model->getDatatable($_GET));
        exit;
    }

    public function create(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        $units = $this->model->getUnits();
        $errors = [];
        $oldInput = [];
        require __DIR__ . '/../../views/periode/create.php';
    }

    public function store(): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /periods/create'); exit;
        }

        $data = [
            'unit_id' => (int)$_POST['unit_id'],
            'nama_periode' => trim($_POST['nama_periode']),
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai']
        ];

        $errors = [];
        if (empty($data['nama_periode'])) $errors[] = "Nama periode wajib diisi.";
        if ($data['tanggal_mulai'] >= $data['tanggal_selesai']) {
            $errors[] = "Tanggal selesai harus lebih besar dari tanggal mulai.";
        }

        // Validasi Overlap
        if ($this->model->hasOverlap($data['unit_id'], $data['tanggal_mulai'], $data['tanggal_selesai'])) {
            $errors[] = "Periode ini tumpang tindih dengan periode lain pada unit yang sama.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: /periods/create'); exit;
        }

        try {
            $id = $this->model->create($data);
            $this->auditLog->log('PERIODE_CREATE', "Membuat periode: {$data['nama_periode']}");
            $_SESSION['flash_success'] = "Periode berhasil dibuat.";
            header('Location: /periods');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = ["Gagal menyimpan: " . $e->getMessage()];
            header('Location: /periods/create');
        }
        exit;
    }

    public function show(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin', 'Operator']);
        $periode = $this->model->findById($id);
        if (!$periode) { http_response_code(404); die("Data tidak ditemukan"); }
        
        $hasData = $this->model->hasSurveyData($id);
        require __DIR__ . '/../../views/periode/show.php';
    }

    public function edit(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        $periode = $this->model->findById($id);
        if (!$periode) { http_response_code(404); die("Data tidak ditemukan"); }
        
        $units = $this->model->getUnits();
        $errors = [];
        require __DIR__ . '/../../views/periode/edit.php';
    }

    public function update(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /periods/{$id}/edit"); exit;
        }

        $data = [
            'unit_id' => (int)$_POST['unit_id'],
            'nama_periode' => trim($_POST['nama_periode']),
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai']
        ];

        $errors = [];
        if ($data['tanggal_mulai'] >= $data['tanggal_selesai']) {
            $errors[] = "Tanggal selesai harus lebih besar dari tanggal mulai.";
        }

        // Validasi Overlap (exclude diri sendiri)
        if ($this->model->hasOverlap($data['unit_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], $id)) {
            $errors[] = "Periode ini tumpang tindih dengan periode lain pada unit yang sama.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = $errors;
            header("Location: /periods/{$id}/edit"); exit;
        }

        try {
            $this->model->update($id, $data);
            $this->auditLog->log('PERIODE_UPDATE', "Update periode ID: {$id}");
            $_SESSION['flash_success'] = "Periode diperbarui.";
            header("Location: /periods/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = ["Gagal memperbarui: " . $e->getMessage()];
            header("Location: /periods/{$id}/edit");
        }
        exit;
    }

    public function destroy(int $id): void
    {
        $this->checkPermission(['Super Admin', 'Admin']);
        header('Content-Type: application/json');

        if ($this->model->hasSurveyData($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus periode yang sudah memiliki data survei.']);
            exit;
        }

        $this->model->delete($id);
        $this->auditLog->log('PERIODE_DELETE', "Hapus periode ID: {$id}");
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Endpoint manual untuk trigger scheduler (untuk testing)
     */
    public function runScheduler(): void
    {
        $this->checkPermission(['Super Admin']);
        header('Content-Type: application/json');
        
        $scheduler = new PeriodeStatusScheduler($this->model, $this->db);
        $result = $scheduler->syncStatuses();

        echo json_encode([
            'success' => true,
            'message' => "Scheduler berjalan. " . count($result) . " periode diubah statusnya.",
            'changes' => $result
        ]);
        exit;
    }

    private function checkPermission(array $roles): void
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles)) {
            http_response_code(403);
            die("Akses ditolak.");
        }
    }
}
