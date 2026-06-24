<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

/**
 * OAuthController - Handles OAuth2 authentication (Google, etc.)
 */
class OAuthController extends BaseController
{
    protected $authModel;
    protected $session;
    protected $oauthConfig;

    public function __construct()
    {
        $this->authModel = new \App\Models\UserModel();
        $this->session = service('session');
        $this->oauthConfig = config('OAuth2');
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider)
    {
        // Validasi provider
        $allowedProviders = ['google', 'facebook', 'github'];
        if (!in_array(strtolower($provider), $allowedProviders)) {
            return redirect()->to('/auth/login')->with('error', 'Provider OAuth tidak didukung.');
        }

        // Cek konfigurasi OAuth
        if (!$this->oauthConfig->enabled ?? false) {
            return redirect()->to('/auth/login')->with('error', 'OAuth tidak diaktifkan.');
        }

        // Generate state token untuk keamanan
        $state = bin2hex(random_bytes(16));
        $this->session->setTempdata('oauth_state', $state, 300);

        // Build authorization URL (contoh untuk Google)
        $authUrl = $this->buildAuthUrl($provider, $state);

        return redirect()->to($authUrl);
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider)
    {
        // Verifikasi state
        $state = $this->request->getGet('state');
        if ($state !== $this->session->getTempdata('oauth_state')) {
            return redirect()->to('/auth/login')->with('error', 'Invalid state parameter.');
        }

        $code = $this->request->getGet('code');
        if (!$code) {
            return redirect()->to('/auth/login')->with('error', 'Authorization code tidak ditemukan.');
        }

        // Exchange code for access token
        $accessToken = $this->exchangeCodeForToken($provider, $code);
        
        if (!$accessToken) {
            return redirect()->to('/auth/login')->with('error', 'Gagal mendapatkan access token.');
        }

        // Get user info from provider
        $userInfo = $this->getUserInfo($provider, $accessToken);

        if (!$userInfo) {
            return redirect()->to('/auth/login')->with('error', 'Gagal mendapatkan informasi user.');
        }

        // Cari atau buat user lokal
        $user = $this->findOrCreateUser($provider, $userInfo);

        if (!$user) {
            return redirect()->to('/auth/login')->with('error', 'Gagal membuat/mencari user.');
        }

        // Login user
        $this->loginUser($user);

        return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil dengan ' . ucfirst($provider));
    }

    /**
     * Build authorization URL
     */
    protected function buildAuthUrl(string $provider, string $state): string
    {
        switch (strtolower($provider)) {
            case 'google':
                $params = [
                    'client_id'     => $this->oauthConfig->google['client_id'] ?? '',
                    'redirect_uri'  => site_url('auth/oauth/callback/google'),
                    'response_type' => 'code',
                    'scope'         => 'email profile',
                    'state'         => $state,
                ];
                return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            
            default:
                return '/auth/login';
        }
    }

    /**
     * Exchange authorization code for access token
     */
    protected function exchangeCodeForToken(string $provider, string $code)
    {
        // Implementasi sederhana menggunakan cURL
        $tokenUrl = '';
        $params = [];

        switch (strtolower($provider)) {
            case 'google':
                $tokenUrl = 'https://oauth2.googleapis.com/token';
                $params = [
                    'client_id'     => $this->oauthConfig->google['client_id'] ?? '',
                    'client_secret' => $this->oauthConfig->google['client_secret'] ?? '',
                    'redirect_uri'  => site_url('auth/oauth/callback/google'),
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                ];
                break;
        }

        if (!$tokenUrl) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        return $result['access_token'] ?? false;
    }

    /**
     * Get user info from OAuth provider
     */
    protected function getUserInfo(string $provider, string $accessToken)
    {
        $userInfoUrl = '';

        switch (strtolower($provider)) {
            case 'google':
                $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
                break;
        }

        if (!$userInfoUrl) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Find or create user from OAuth info
     */
    protected function findOrCreateUser(string $provider, array $userInfo)
    {
        $oauthId = $userInfo['id'] ?? null;
        $email = $userInfo['email'] ?? null;

        if (!$oauthId || !$email) {
            return false;
        }

        // Cari user berdasarkan OAuth ID
        $user = $this->authModel->where('oauth_provider', $provider)
                                ->where('oauth_id', $oauthId)
                                ->first();

        if ($user) {
            return $user;
        }

        // Cari user berdasarkan email
        $user = $this->authModel->findByEmail($email);

        if ($user) {
            // Update dengan OAuth info
            $this->authModel->update($user['id_pengguna'], [
                'oauth_provider' => $provider,
                'oauth_id'       => $oauthId,
            ]);
            return $user;
        }

        // Buat user baru
        $newUserData = [
            'username'       => $email,
            'email'          => $email,
            'nama_lengkap'   => $userInfo['name'] ?? $userInfo['given_name'] ?? '',
            'password_hash'  => password_encode(bin2hex(random_bytes(16))), // Random password
            'role'           => 'operator',
            'is_active'      => 1,
            'oauth_provider' => $provider,
            'oauth_id'       => $oauthId,
        ];

        $userId = $this->authModel->insert($newUserData);
        
        if ($userId) {
            return $this->authModel->find($userId);
        }

        return false;
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
            'id_unit'     => $user['id_unit'] ?? 0,
            'isLoggedIn'  => true,
        ];

        $this->session->set($userData);
        
        $this->authModel->update($user['id_pengguna'], [
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }
}
