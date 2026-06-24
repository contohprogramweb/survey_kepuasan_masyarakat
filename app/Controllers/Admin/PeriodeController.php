<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PeriodeModel;

/**
 * PeriodeController
 * 
 * Controller untuk manajemen periode survei (UC-04)
 * Mengelola periode aktif, tanggal buka-tutup survei
 */
class PeriodeController extends BaseController
{
    protected PeriodeModel $periodeModel;
    protected $validationRules = [
        'nama_periode' => 'required|min_length[3]|max_length[100]',
        'tanggal_mulai' => 'required|valid_date',
        'tanggal_selesai' => 'required|valid_date|matches[tanggal_mulai]',
        'status' => 'permit_empty|in_list[aktif,nonaktif]',
        'deskripsi' => 'permit_empty|max_length[500]',
    ];

    public function __construct()
    {
        $this->periodeModel = new PeriodeModel();
    }

    /**
     * Display list of periods
     */
    public function index(): string
    {
        $data = [
            'title' => 'Manajemen Periode Survei',
            'periods' => $this->periodeModel
                ->orderBy('tanggal_mulai', 'DESC')
                ->findAll(),
            'activePeriod' => $this->periodeModel->where('status', 'aktif')->first(),
        ];

        return view('admin/periods/index', $data);
    }

    /**
     * Show form to create new period
     */
    public function new(): string
    {
        $data = [
            'title' => 'Tambah Periode Baru',
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/periods/new', $data);
    }

    /**
     * Create new period
     */
    public function create()
    {
        // Validate input
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check for overlapping periods
        $startDate = $this->request->getPost('tanggal_mulai');
        $endDate = $this->request->getPost('tanggal_selesai');
        
        $overlap = $this->periodeModel->where(function($builder) use ($startDate, $endDate) {
            return $builder->groupStart()
                ->where('tanggal_mulai <=', $endDate)
                ->where('tanggal_selesai >=', $startDate)
                ->groupEnd();
        })->first();

        if ($overlap) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Periode baru tumpang tindih dengan periode yang sudah ada.');
        }

        // If status is 'aktif', deactivate other periods first
        if ($this->request->getPost('status') === 'aktif') {
            $this->periodeModel->update(null, ['status' => 'nonaktif']);
        }

        // Insert new period
        $this->periodeModel->insert([
            'nama_periode' => $this->request->getPost('nama_periode'),
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'status' => $this->request->getPost('status') ?? 'nonaktif',
            'deskripsi' => $this->request->getPost('deskripsi'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Log audit trail
        log_message('info', 'Periode baru dibuat: ' . $this->request->getPost('nama_periode'));

        return redirect()->to('admin/periods')->with('success', 'Periode berhasil ditambahkan.');
    }

    /**
     * Show form to edit period
     */
    public function edit($id): string
    {
        $periode = $this->periodeModel->find($id);
        
        if (!$periode) {
            return redirect()->to('admin/periods')->with('error', 'Periode tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Periode',
            'periode' => $periode,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/periods/edit', $data);
    }

    /**
     * Update period
     */
    public function update($id)
    {
        $periode = $this->periodeModel->find($id);
        
        if (!$periode) {
            return redirect()->to('admin/periods')->with('error', 'Periode tidak ditemukan.');
        }

        // Validate input
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check for overlapping periods (exclude current period)
        $startDate = $this->request->getPost('tanggal_mulai');
        $endDate = $this->request->getPost('tanggal_selesai');
        
        $overlap = $this->periodeModel
            ->where('id_periode !=', $id)
            ->where(function($builder) use ($startDate, $endDate) {
                return $builder->groupStart()
                    ->where('tanggal_mulai <=', $endDate)
                    ->where('tanggal_selesai >=', $startDate)
                    ->groupEnd();
            })
            ->first();

        if ($overlap) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Periode tumpang tindih dengan periode lain.');
        }

        // If status changed to 'aktif', deactivate other periods
        if ($this->request->getPost('status') === 'aktif' && $periode['status'] !== 'aktif') {
            $this->periodeModel
                ->where('id_periode !=', $id)
                ->update(null, ['status' => 'nonaktif']);
        }

        // Update period
        $this->periodeModel->update($id, [
            'nama_periode' => $this->request->getPost('nama_periode'),
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'status' => $this->request->getPost('status'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log audit trail
        log_message('info', 'Periode diperbarui: ' . $this->request->getPost('nama_periode'));

        return redirect()->to('admin/periods')->with('success', 'Periode berhasil diperbarui.');
    }

    /**
     * Delete period
     */
    public function delete($id)
    {
        $periode = $this->periodeModel->find($id);
        
        if (!$periode) {
            return redirect()->to('admin/periods')->with('error', 'Periode tidak ditemukan.');
        }

        // Check if period has responses
        $db = \Config\Database::connect();
        $responseCount = $db->table('tb_respons_survei')
            ->where('id_periode', $id)
            ->countAllResults();

        if ($responseCount > 0) {
            return redirect()->to('admin/periods')
                ->with('error', 'Tidak dapat menghapus periode yang sudah memiliki respons survei.');
        }

        // Delete period
        $this->periodeModel->delete($id);

        // Log audit trail
        log_message('info', 'Periode dihapus: ' . $periode['nama_periode']);

        return redirect()->to('admin/periods')->with('success', 'Periode berhasil dihapus.');
    }

    /**
     * Toggle period status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        $periode = $this->periodeModel->find($id);
        
        if (!$periode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Periode tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $newStatus = $periode['status'] === 'aktif' ? 'nonaktif' : 'aktif';

        // If activating, deactivate other periods first
        if ($newStatus === 'aktif') {
            $this->periodeModel->update(null, ['status' => 'nonaktif']);
        }

        // Update status
        $this->periodeModel->update($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log audit trail
        log_message('info', "Status periode {$periode['nama_periode']} diubah menjadi {$newStatus}");

        return $this->response->setJSON([
            'success' => true,
            'message' => "Status periode berhasil diubah menjadi {$newStatus}.",
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Get period details as JSON (for API/AJAX)
     */
    public function show($id)
    {
        $periode = $this->periodeModel->find($id);
        
        if (!$periode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Periode tidak ditemukan.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $periode,
        ]);
    }
}
