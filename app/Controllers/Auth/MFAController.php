<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

/**
 * MFAController - Handles Multi-Factor Authentication
 */
class MFAController extends BaseController
{
    protected $authModel;
    protected $session;

    public function __construct()
    {
        $this->authModel = new \App\Models\UserModel();
        $this->session = service('session');
    }

    /**
     * Show MFA setup page
     */
    public function setup()
    {
        $userId = $this->session->get('id_pengguna');
        if (!$userId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $this->authModel->findById($userId);
        
        // Generate secret jika belum ada
        if (empty($user['mfa_secret'])) {
            // Asumsi menggunakan library OTP
            $secret = $this->generateSecret();
            $this->authModel->update($userId, ['mfa_secret' => $secret]);
            $user['mfa_secret'] = $secret;
        }

        $qrCodeUrl = $this->generateQRCode($user['email'], $user['mfa_secret']);

        return view('auth/mfa_setup', [
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user['mfa_secret']
        ]);
    }

    /**
     * Enable MFA for user
     */
    public function enable()
    {
        $userId = $this->session->get('id_pengguna');
        $code = $this->request->getPost('code');

        if ($this->verifyCode($userId, $code)) {
            $this->authModel->update($userId, ['mfa_enabled' => 1]);
            return redirect()->to('/admin/dashboard')->with('success', 'MFA berhasil diaktifkan.');
        }

        return redirect()->back()->with('error', 'Kode MFA tidak valid.');
    }

    /**
     * Show MFA verification page
     */
    public function verify()
    {
        $pendingUserId = $this->session->getTempdata('pending_user_id');
        if (!$pendingUserId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return view('auth/mfa_verify');
    }

    /**
     * Validate MFA code
     */
    public function validate()
    {
        $pendingUserId = $this->session->getTempdata('pending_user_id');
        $code = $this->request->getPost('code');

        if (!$pendingUserId) {
            return redirect()->to('/auth/login')->with('error', 'Sesi expired, silakan login ulang.');
        }

        if ($this->verifyCode($pendingUserId, $code)) {
            $user = $this->authModel->findById($pendingUserId);
            
            // Clear temp data
            $this->session->removeTempdata('pending_user_id');
            
            // Login user
            $this->loginUser($user);
            
            return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil!');
        }

        return redirect()->back()->with('error', 'Kode MFA tidak valid.');
    }

    /**
     * Generate secret key untuk MFA
     */
    protected function generateSecret(): string
    {
        // Simple base32-like secret generation
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * Generate QR Code URL untuk Google Authenticator
     */
    protected function generateQRCode(string $email, string $secret): string
    {
        $issuer = urlencode('Sistem IKM');
        $label = urlencode($email);
        return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}";
    }

    /**
     * Verify MFA code
     */
    protected function verifyCode(int $userId, string $code): bool
    {
        $user = $this->authModel->findById($userId);
        if (!$user || empty($user['mfa_secret'])) {
            return false;
        }

        // Simple TOTP verification (asumsi implementasi dasar)
        // Dalam production, gunakan library seperti php-otplib
        $currentToken = $this->getCurrentTOTP($user['mfa_secret']);
        return $currentToken === $code;
    }

    /**
     * Generate current TOTP token
     */
    protected function getCurrentTOTP(string $secret): string
    {
        // Implementasi sederhana TOTP (untuk production gunakan library)
        $timeSlice = floor(time() / 30);
        $hash = hash_hmac('sha1', pack('N*', 0, 0, 0, $timeSlice), base32_decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = ((ord($hash[$offset]) & 0x7F) << 24 |
                 (ord($hash[$offset + 1]) & 0xFF) << 16 |
                 (ord($hash[$offset + 2]) & 0xFF) << 8 |
                 (ord($hash[$offset + 3]) & 0xFF)) % 1000000;
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Login user helper
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
        
        $this->authModel->update($user['id_pengguna'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }
}

// Helper function untuk base32 decode
if (!function_exists('base32_decode')) {
    function base32_decode($str) {
        $encoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $str = strtoupper(str_replace('=', '', $str));
        $len = strlen($str);
        $bits = 0;
        $val = 0;
        $decoded = '';
        for ($i = 0; $i < $len; $i++) {
            $val = ($val << 5) | strpos($encoding, $str[$i]);
            $bits += 5;
            if ($bits >= 8) {
                $decoded .= chr(($val >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }
        return $decoded;
    }
}
