<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;

class JwtService
{
    private string $privateKeyPath;
    private string $publicKeyPath;
    private string $passphrase;
    private int $accessTokenTtl; // in seconds (default 30 minutes)
    private int $refreshTokenTtl; // in seconds (default 7 days)
    
    // Token storage for refresh token rotation
    private array $revokedTokens = [];
    private array $activeRefreshTokens = [];

    public function __construct(
        string $privateKeyPath,
        string $publicKeyPath,
        string $passphrase = '',
        int $accessTokenTtl = 1800, // 30 minutes
        int $refreshTokenTtl = 604800 // 7 days
    ) {
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath = $publicKeyPath;
        $this->passphrase = $passphrase;
        $this->accessTokenTtl = $accessTokenTtl;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    /**
     * Generate access token and refresh token for a user
     */
    public function generateTokens(User $user, ?string $mfaVerified = null): array
    {
        $now = new \DateTimeImmutable();
        
        // Access token claims
        $accessClaims = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->modify("+$this->accessTokenTtl seconds")->getTimestamp(),
            'jti' => bin2hex(random_bytes(16)),
            'type' => 'access',
            'sub' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'mfa_verified' => $mfaVerified ?? false,
        ];

        // Sign access token with RS256
        $accessToken = JWT::encode($accessClaims, $this->getPrivateKey(), 'RS256');

        // Refresh token claims
        $refreshJti = bin2hex(random_bytes(16));
        $refreshClaims = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->modify("+$this->refreshTokenTtl seconds")->getTimestamp(),
            'jti' => $refreshJti,
            'type' => 'refresh',
            'sub' => (string) $user->getId(),
            'username' => $user->getUsername(),
        ];

        // Sign refresh token with RS256
        $refreshToken = JWT::encode($refreshClaims, $this->getPrivateKey(), 'RS256');

        // Store refresh token for rotation tracking
        $this->activeRefreshTokens[$refreshJti] = [
            'user_id' => $user->getId(),
            'created_at' => $now,
            'token' => $refreshToken,
        ];

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->accessTokenTtl,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Verify and decode access token
     */
    public function verifyAccessToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, $this->getPublicKey());
            
            // Check if token is revoked
            if (isset($this->revokedTokens[$decoded->jti])) {
                throw new \Exception('Token has been revoked');
            }

            // Verify token type
            if ($decoded->type !== 'access') {
                throw new \Exception('Invalid token type');
            }

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired access token: ' . $e->getMessage());
        }
    }

    /**
     * Verify and decode refresh token
     */
    public function verifyRefreshToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, $this->getPublicKey());
            
            // Check if token is revoked
            if (isset($this->revokedTokens[$decoded->jti])) {
                throw new \Exception('Token has been revoked');
            }

            // Verify token type
            if ($decoded->type !== 'refresh') {
                throw new \Exception('Invalid token type');
            }

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token (with rotation)
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        // Verify the refresh token
        $claims = $this->verifyRefreshToken($refreshToken);
        
        // Revoke the old refresh token (rotation)
        $this->revokeToken($claims['jti']);

        // Create a mock user object for token generation
        $user = new User(
            (int) $claims['sub'],
            $claims['username'],
            $claims['username'] . '@example.com',
            ''
        );

        // Generate new tokens
        return $this->generateTokens($user);
    }

    /**
     * Revoke a token (for logout or token rotation)
     */
    public function revokeToken(string $jti): void
    {
        $this->revokedTokens[$jti] = new \DateTimeImmutable();
        
        // Remove from active refresh tokens
        if (isset($this->activeRefreshTokens[$jti])) {
            unset($this->activeRefreshTokens[$jti]);
        }
    }

    /**
     * Revoke all tokens for a user
     */
    public function revokeAllUserTokens(int $userId): void
    {
        foreach ($this->activeRefreshTokens as $jti => $tokenData) {
            if ($tokenData['user_id'] === $userId) {
                $this->revokeToken($jti);
            }
        }
    }

    /**
     * Get private key resource
     */
    private function getPrivateKey(): \OpenSSLAsymmetricKey
    {
        if (!file_exists($this->privateKeyPath)) {
            throw new \RuntimeException('Private key file not found: ' . $this->privateKeyPath);
        }
        
        $privateKey = file_get_contents($this->privateKeyPath);
        $key = openssl_pkey_get_private($privateKey, $this->passphrase);
        
        if ($key === false) {
            throw new \RuntimeException('Failed to load private key: ' . openssl_error_string());
        }
        
        return $key;
    }

    /**
     * Get public key resource
     */
    private function getPublicKey(): Key
    {
        if (!file_exists($this->publicKeyPath)) {
            throw new \RuntimeException('Public key file not found: ' . $this->publicKeyPath);
        }
        
        $publicKey = file_get_contents($this->publicKeyPath);
        return new Key($publicKey, 'RS256');
    }

    /**
     * Get remaining time before token expiration
     */
    public function getTokenRemainingTime(string $token): int
    {
        try {
            $claims = $this->verifyAccessToken($token);
            $now = new \DateTimeImmutable();
            return max(0, $claims['exp'] - $now->getTimestamp());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if a token is about to expire (within 5 minutes)
     */
    public function isTokenExpiringSoon(string $token, int $threshold = 300): bool
    {
        return $this->getTokenRemainingTime($token) < $threshold;
    }

    /**
     * Generate RSA key pair for JWT signing
     */
    public static function generateKeyPair(string $privateKeyPath, string $publicKeyPath, string $passphrase = ''): void
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        
        if ($res === false) {
            throw new \RuntimeException('Failed to generate key pair: ' . openssl_error_string());
        }

        // Export private key
        openssl_pkey_export($res, $privateKey, $passphrase);
        
        // Export public key
        $publicKey = openssl_pkey_get_details($res)['key'];

        // Save keys to files
        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);

        // Set appropriate permissions
        chmod($privateKeyPath, 0600);
        chmod($publicKeyPath, 0644);
    }
}
