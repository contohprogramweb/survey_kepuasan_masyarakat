<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * UserController - Admin user management
 */
class UserController extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = service('session');
    }

    /**
     * Display list of users
     */
    public function index()
    {
        $users = $this->userModel->getUsersWithUnit();
        
        return view('admin/users/index', [
            'users' => $users,
            'title' => 'Manajemen Pengguna'
        ]);
    }

    /**
     * Show create user form
     */
    public function new()
    {
        return view('admin/users/new', [
            'title' => 'Tambah Pengguna Baru'
        ]);
    }

    /**
     * Create new user
     */
    public function create()
    {
        $rules = [
            'username'       => 'required|alpha_numeric_space|min_length[3]|max_length[100]|is_unique[tb_pengguna.username]',
            'email'          => 'required|valid_email|is_unique[tb_pengguna.email]',
            'password'       => 'required|min_length[6]',
            'nama_lengkap'   => 'required|max_length[255]',
            'role'           => 'required|in_list[super_admin,admin_unit,operator,viewer]',
            'id_unit'        => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username'      => $this->request->getPost('username'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_encode($this->request->getPost('password')),
            'nama_lengkap'  => $this->request->getPost('nama_lengkap'),
            'role'          => $this->request->getPost('role'),
            'id_unit'       => $this->request->getPost('id_unit'),
            'is_active'     => 1,
        ];

        $userId = $this->userModel->insert($data);

        if ($userId) {
            return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pengguna.');
    }

    /**
     * Show edit user form
     */
    public function edit(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('admin/users/edit', [
            'user' => $user,
            'title' => 'Edit Pengguna'
        ]);
    }

    /**
     * Update user
     */
    public function update(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $rules = [
            'username'       => "required|alpha_numeric_space|min_length[3]|max_length[100]|is_unique[tb_pengguna.username,id_pengguna,{$id}]",
            'email'          => "required|valid_email|is_unique[tb_pengguna.email,id_pengguna,{$id}]",
            'nama_lengkap'   => 'required|max_length[255]',
            'role'           => 'required|in_list[super_admin,admin_unit,operator,viewer]',
            'id_unit'        => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username'     => $this->request->getPost('username'),
            'email'        => $this->request->getPost('email'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role'         => $this->request->getPost('role'),
            'id_unit'      => $this->request->getPost('id_unit'),
        ];

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password_hash'] = password_encode($password);
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil diupdate.');
    }

    /**
     * Delete user
     */
    public function delete(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Tidak boleh menghapus diri sendiri
        if ($user['id_pengguna'] === $this->session->get('id_pengguna')) {
            return redirect()->to('/admin/users')->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($id);

        return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ]);
        }

        // Tidak boleh menonaktifkan diri sendiri
        if ($user['id_pengguna'] === $this->session->get('id_pengguna')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menonaktifkan akun Anda sendiri.'
            ]);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => $newStatus ? 'Pengguna diaktifkan.' : 'Pengguna dinonaktifkan.',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Generate random password
        $newPassword = bin2hex(random_bytes(4)); // 8 karakter
        
        $this->userModel->resetPassword($id, $newPassword);

        return redirect()->to('/admin/users')->with('success', "Password direset. Password sementara: {$newPassword}");
    }

    /**
     * Get user detail as JSON
     */
    public function show(int $id)
    {
        $user = $this->userModel->findById($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ]);
        }

        unset($user['password_hash']);

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }
}
