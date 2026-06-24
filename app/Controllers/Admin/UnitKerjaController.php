<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UnitLayananModel;

/**
 * UnitKerjaController - Admin unit kerja management
 * Handles CRUD operations for unit kerja/layanan
 */
class UnitKerjaController extends BaseController
{
    protected $unitModel;
    protected $session;

    public function __construct()
    {
        $this->unitModel = new UnitLayananModel();
        $this->session = service('session');
    }

    /**
     * Display list of unit kerja
     */
    public function index()
    {
        $units = $this->unitModel->getAllWithInstansi();
        
        return view('admin/unit-kerja/index', [
            'units' => $units,
            'title' => 'Manajemen Unit Kerja'
        ]);
    }

    /**
     * Get unit kerja as JSON
     */
    public function data()
    {
        $units = $this->unitModel->getAllWithInstansi();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $units
        ]);
    }

    /**
     * Show create unit form
     */
    public function new()
    {
        $instansiList = $this->unitModel->getAllInstansi();
        
        return view('admin/unit-kerja/new', [
            'instansiList' => $instansiList,
            'title' => 'Tambah Unit Baru'
        ]);
    }

    /**
     * Create new unit
     */
    public function create()
    {
        $rules = [
            'id_instansi'  => 'required|integer',
            'kode_unit'    => 'required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[tb_unit_layanan.kode_unit]',
            'nama_unit'    => 'required|max_length[255]',
            'alamat'       => 'permit_empty|max_length[500]',
            'telepon'      => 'permit_empty|max_length[20]',
            'email'        => 'permit_empty|valid_email',
            'jenis_unit'   => 'required|in_list[pusat,cabang,pembantu]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_instansi' => $this->request->getPost('id_instansi'),
            'kode_unit'   => strtoupper($this->request->getPost('kode_unit')),
            'nama_unit'   => $this->request->getPost('nama_unit'),
            'alamat'      => $this->request->getPost('alamat'),
            'telepon'     => $this->request->getPost('telepon'),
            'email'       => $this->request->getPost('email'),
            'jenis_unit'  => $this->request->getPost('jenis_unit'),
            'is_active'   => 1,
        ];

        $unitId = $this->unitModel->insert($data);

        if ($unitId) {
            return redirect()->to('/admin/unit-kerja')->with('success', 'Unit berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan unit.');
    }

    /**
     * Show edit unit form
     */
    public function edit(int $id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Unit tidak ditemukan.');
        }

        $instansiList = $this->unitModel->getAllInstansi();
        
        return view('admin/unit-kerja/edit', [
            'unit' => $unit,
            'instansiList' => $instansiList,
            'title' => 'Edit Unit'
        ]);
    }

    /**
     * Update unit
     */
    public function update(int $id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Unit tidak ditemukan.');
        }

        $rules = [
            'id_instansi'  => 'required|integer',
            'kode_unit'    => "required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[tb_unit_layanan.kode_unit,id_unit,{$id}]",
            'nama_unit'    => 'required|max_length[255]',
            'alamat'       => 'permit_empty|max_length[500]',
            'telepon'      => 'permit_empty|max_length[20]',
            'email'        => 'permit_empty|valid_email',
            'jenis_unit'   => 'required|in_list[pusat,cabang,pembantu]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_instansi' => $this->request->getPost('id_instansi'),
            'kode_unit'   => strtoupper($this->request->getPost('kode_unit')),
            'nama_unit'   => $this->request->getPost('nama_unit'),
            'alamat'      => $this->request->getPost('alamat'),
            'telepon'     => $this->request->getPost('telepon'),
            'email'       => $this->request->getPost('email'),
            'jenis_unit'  => $this->request->getPost('jenis_unit'),
        ];

        $this->unitModel->update($id, $data);

        return redirect()->to('/admin/unit-kerja')->with('success', 'Unit berhasil diupdate.');
    }

    /**
     * Delete unit
     */
    public function delete(int $id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Unit tidak ditemukan.');
        }

        // Check if unit has users
        if ($this->unitModel->hasUsers($id)) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Tidak dapat menghapus unit yang masih memiliki pengguna.');
        }

        // Check if unit has survey responses
        if ($this->unitModel->hasResponses($id)) {
            return redirect()->to('/admin/unit-kerja')->with('error', 'Tidak dapat menghapus unit yang masih memiliki respons survei.');
        }

        $this->unitModel->delete($id);

        return redirect()->to('/admin/unit-kerja')->with('success', 'Unit berhasil dihapus.');
    }

    /**
     * Toggle unit active status
     */
    public function toggleStatus(int $id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unit tidak ditemukan.'
            ]);
        }

        $newStatus = $unit['is_active'] ? 0 : 1;
        $this->unitModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Unit diaktifkan.' : 'Unit dinonaktifkan.',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Get unit detail as JSON
     */
    public function show(int $id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unit tidak ditemukan.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $unit
        ]);
    }
}
