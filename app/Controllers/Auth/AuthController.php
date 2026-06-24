<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\InstansiModel;

/**
 * AuthController - Handles user authentication
 */
class AuthController extends BaseController
{
    protected $authModel;
    protected $session;

    public function __construct()
    {
        // Load model pengguna jika ada
        $this->authModel = new \App\Models\UserModel();
        $this->session = service('session');
    }

    /**
     * Show login page
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Authenticate user credentials
     */
    public function authenticate()
    {
        // Validasi input
        $rules = [
            'username' => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Coba autentikasi dengan model
        $user = $this->authModel->findByUsername($username);

        if (!$user || !$user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan atau akun tidak aktif.');
        }

        // Verifikasi password
        if (!password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Password salah.');
        }

        // Cek MFA jika diaktifkan
        if ($user['mfa_enabled']) {
            $this->session->setTempdata('pending_user_id', $user['id_pengguna'], 300);
            return redirect()->to('/auth/mfa/verify');
        }

        // Login berhasil
        $this->loginUser($user);

        return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil!');
    }

    /**
     * Login user dan set session
     */
    protected function loginUser($user)
    {
        $userData = [
            'id_pengguna' => $user['id_pengguna'],
            'username'    => $user['username'],
            'email'       => $user['email'],
            'nama_lengkap'=> $user['nama_lengkap'],
            'role'        => $user['role'],
            'id_unit'     => $user['id_unit'],
            'isLoggedIn'  => true,
        ];

        $this->session->set($userData);
        
        // Update last login
        $this->authModel->update($user['id_pengguna'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/auth/login')->with('success', 'Anda telah logout.');
    }
}
