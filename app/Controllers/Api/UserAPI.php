<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * UserAPI Controller
 * 
 * RESTful API untuk manajemen pengguna
 * Endpoint: /api/users/*
 */
class UserAPI extends BaseController
{
    protected UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    /**
     * GET /api/users
     * Get list users (requires authentication)
     */
    public function index()
    {
        // Check authentication
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $users = $this->userModel->findAll();
        
        // Remove sensitive data
        foreach ($users as &$user) {
            unset($user['password']);
            unset($user['mfa_secret']);
            unset($user['reset_token']);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $users
        ]);
    }
    
    /**
     * GET /api/users/{id}
     * Get user by ID
     */
    public function show($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }
        
        unset($user['password'], $user['mfa_secret'], $user['reset_token']);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }
    
    /**
     * POST /api/users
     * Create new user
     */
    public function create()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $json = $this->request->getJSON(true);
        
        if (!$json) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid JSON format'
            ])->setStatusCode(400);
        }
        
        // Validate required fields
        if (empty($json['username']) || empty($json['email']) || empty($json['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username, email, dan password wajib diisi'
            ])->setStatusCode(400);
        }
        
        try {
            $userId = $this->userModel->insert([
                'username' => $json['username'],
                'email' => $json['email'],
                'password' => password_hash($json['password'], PASSWORD_DEFAULT),
                'nama_lengkap' => $json['nama_lengkap'] ?? null,
                'id_unit' => $json['id_unit'] ?? null,
                'id_role' => $json['id_role'] ?? 3, // Default: operator
                'status' => $json['status'] ?? 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => ['id_pengguna' => $userId]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'API User Create Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat user'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * PUT /api/users/{id}
     * Update user
     */
    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }
        
        $json = $this->request->getJSON(true);
        
        $updateData = [];
        if (isset($json['nama_lengkap'])) $updateData['nama_lengkap'] = $json['nama_lengkap'];
        if (isset($json['email'])) $updateData['email'] = $json['email'];
        if (isset($json['id_unit'])) $updateData['id_unit'] = $json['id_unit'];
        if (isset($json['id_role'])) $updateData['id_role'] = $json['id_role'];
        if (isset($json['status'])) $updateData['status'] = $json['status'];
        if (isset($json['password'])) {
            $updateData['password'] = password_hash($json['password'], PASSWORD_DEFAULT);
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->userModel->update($id, $updateData);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'User berhasil diupdate'
        ]);
    }
    
    /**
     * DELETE /api/users/{id}
     * Delete user
     */
    public function delete($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }
        
        // Prevent deleting own account
        if ($user['id_pengguna'] == session()->get('id_pengguna')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun Anda sendiri'
            ])->setStatusCode(403);
        }
        
        $this->userModel->delete($id);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
    
    /**
     * POST /api/users/{id}/toggle-status
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }
        
        $newStatus = $user['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        
        $this->userModel->update($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => "Status user diubah menjadi {$newStatus}",
            'new_status' => $newStatus
        ]);
    }
}
