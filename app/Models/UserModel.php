<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel - Model untuk manajemen user/pengguna
 */
class UserModel extends Model
{
    protected $table            = 'tb_pengguna';
    protected $primaryKey       = 'id_pengguna';
    protected $allowedFields    = [
        'id_unit',
        'username',
        'password_hash',
        'nama_lengkap',
        'email',
        'role',
        'mfa_secret',
        'mfa_enabled',
        'oauth_provider',
        'oauth_id',
        'last_login',
        'is_active'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    
    protected $validationRules  = [
        'username'      => 'required|alpha_numeric_space|min_length[3]|max_length[100]|is_unique[tb_pengguna.username,id_pengguna,{id_pengguna}]',
        'email'         => 'required|valid_email|is_unique[tb_pengguna.email,id_pengguna,{id_pengguna}]',
        'password_hash' => 'required|min_length[6]',
        'role'          => 'required|in_list[super_admin,admin_unit,operator,viewer]',
        'id_unit'       => 'required|integer',
    ];
    
    protected $validationMessages = [
        'username' => [
            'required' => 'Username wajib diisi.',
            'is_unique' => 'Username sudah digunakan.',
        ],
        'email' => [
            'required' => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique' => 'Email sudah digunakan.',
        ],
    ];

    /**
     * Find user by username
     */
    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Get users by unit
     */
    public function getByUnit(int $idUnit)
    {
        return $this->where('id_unit', $idUnit)->findAll();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role)
    {
        return $this->where('role', $role)->findAll();
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_encode($newPassword);
        return $this->update($id, ['password_hash' => $passwordHash]);
    }

    /**
     * Get all users with unit info
     */
    public function getUsersWithUnit()
    {
        return $this->select('tb_pengguna.*, tb_unit_layanan.nama_unit')
            ->join('tb_unit_layanan', 'tb_unit_layanan.id_unit = tb_pengguna.id_unit', 'left')
            ->findAll();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(int $userId, string $role): bool
    {
        $user = $this->find($userId);
        return $user && $user['role'] === $role;
    }

    /**
     * Get active users only
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', 1)->findAll();
    }
}
