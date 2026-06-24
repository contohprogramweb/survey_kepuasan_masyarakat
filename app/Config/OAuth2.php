<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi OAuth2 untuk Autentikasi Eksternal
 */
class OAuth2 extends BaseConfig
{
    /**
     * Enable OAuth2 authentication
     */
    public bool $enabled = false;

    /**
     * OAuth2 provider (google, microsoft, github, custom)
     */
    public string $provider = '';

    /**
     * Client credentials
     */
    public string $clientId = '';
    public string $clientSecret = '';
    public string $redirectUri = '';

    /**
     * OAuth2 scopes
     */
    public array $scopes = ['openid', 'profile', 'email'];

    /**
     * Provider-specific configurations
     */
    public array $providers = [
        'google' => [
            'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'userinfo_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'scopes' => ['openid', 'profile', 'email'],
        ],
        'microsoft' => [
            'authorize_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'userinfo_url' => 'https://graph.microsoft.com/v1.0/me',
            'scopes' => ['openid', 'profile', 'email', 'User.Read'],
        ],
        'github' => [
            'authorize_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'userinfo_url' => 'https://api.github.com/user',
            'scopes' => ['user:email'],
        ],
    ];

    /**
     * User mapping dari OAuth2 response ke aplikasi
     */
    public array $userMapping = [
        'id' => 'sub',
        'email' => 'email',
        'name' => 'name',
        'given_name' => 'given_name',
        'family_name' => 'family_name',
        'picture' => 'picture',
    ];

    /**
     * Auto-create user jika belum ada
     */
    public bool $autoCreateUser = true;

    /**
     * Auto-link akun OAuth2 ke user yang sudah ada berdasarkan email
     */
    public bool $autoLinkUser = true;
}
