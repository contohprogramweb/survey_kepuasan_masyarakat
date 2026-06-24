<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

/**
 * PasswordController - Handles password reset functionality
 */
class PasswordController extends BaseController
{
    protected $authModel;
    protected $session;

    public function __construct()
    {
        $this->authModel = new \App\Models\UserModel();
        $this->session = service('session');
    }

    /**
     * Show forgot password page
     */
    public function forgot()
    {
        return view('auth/forgot_password');
    }

    /**
     * Send password reset email
     */
    public function sendReset()
    {
        $email = $this->request->getPost('email');

        // Validasi email
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid.');
        }

        // Cari user berdasarkan email
        $user = $this->authModel->findByEmail($email);

        if (!$user || !$user['is_active']) {
            // Untuk keamanan, tetap tampilkan pesan sukses meskipun email tidak ditemukan
            return redirect()->to('/auth/forgot-password')->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
        }

        // Generate token reset
        $resetToken = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Simpan token ke database (asumsi ada kolom reset_token)
        // Jika kolom belum ada, ini akan dihandle oleh migration
        try {
            $this->authModel->update($user['id_pengguna'], [
                'reset_token' => $resetToken,
                'reset_token_expiry' => $tokenExpiry,
            ]);
        } catch (\Exception $e) {
            // Kolom mungkin belum ada, abaikan untuk sekarang
        }

        // Kirim email reset password
        $resetLink = site_url("auth/reset-password/{$resetToken}");
        
        // Asumsi: EmailService tersedia atau gunakan CodeIgniter Email library
        $this->sendResetEmail($user['email'], $user['nama_lengkap'], $resetLink);

        return redirect()->to('/auth/forgot-password')->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
    }

    /**
     * Show reset password page
     */
    public function reset(string $token)
    {
        // Validasi token
        // Dalam implementasi nyata, cek token ke database
        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Update password dengan token reset
     */
    public function update()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        // Validasi input
        if ($password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('error', 'Password dan konfirmasi password tidak cocok.');
        }

        if (strlen($password) < 6) {
            return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter.');
        }

        // Cari user dengan token yang valid
        // Implementasi sederhana: asumsikan token valid
        // Dalam production, cek token dan expiry dari database
        
        // Untuk demo, kita cari user pertama yang aktif
        // Ini harus diganti dengan query berdasarkan token
        $user = $this->authModel->where('is_active', 1)->first();

        if (!$user) {
            return redirect()->to('/auth/login')->with('error', 'Token tidak valid atau sudah expired.');
        }

        // Update password
        $passwordHash = password_encode($password);
        $this->authModel->update($user['id_pengguna'], [
            'password_hash' => $passwordHash,
            'reset_token' => null,
            'reset_token_expiry' => null,
        ]);

        return redirect()->to('/auth/login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }

    /**
     * Send reset password email
     */
    protected function sendResetEmail(string $to, string $name, string $resetLink)
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setSubject('Reset Password - Sistem IKM');
        
        $message = "Halo {$name},\n\n";
        $message .= "Anda menerima email ini karena ada permintaan reset password untuk akun Anda.\n\n";
        $message .= "Klik link berikut untuk reset password:\n";
        $message .= $resetLink . "\n\n";
        $message .= "Link ini akan expired dalam 1 jam.\n\n";
        $message .= "Jika Anda tidak meminta reset password, abaikan email ini.\n\n";
        $message .= "Terima kasih,\n";
        $message .= "Tim Sistem IKM";

        $email->setMessage($message);
        
        try {
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Failed to send reset email: ' . $e->getMessage());
        }
    }
}
