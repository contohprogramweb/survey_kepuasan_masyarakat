<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * API Auth Filter - Middleware untuk API Authentication
 * Validasi API token/key untuk akses ke API endpoints
 */
class ApiAuthFilter implements FilterInterface
{
    /**
     * Validate API authentication before accessing API endpoints
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip auth untuk public endpoints
        $publicEndpoints = [
            'api/v1/surveys',
            'api/v1/surveys/*',
        ];

        $currentPath = $request->getUri()->getPath();
        
        foreach ($publicEndpoints as $endpoint) {
            if (fnmatch($endpoint, $currentPath)) {
                return;
            }
        }

        // Get API key/token dari header
        $apiKey = $request->getHeaderLine('X-API-Key');
        $bearerToken = $request->getHeaderLine('Authorization');

        if (empty($apiKey) && empty($bearerToken)) {
            return $this->unauthorizedResponse('API key atau Authorization header diperlukan.');
        }

        // Validate API Key
        if (!empty($apiKey)) {
            $isValid = $this->validateApiKey($apiKey);
            
            if (!$isValid) {
                return $this->unauthorizedResponse('API key tidak valid.');
            }
        }

        // Validate Bearer Token (JWT/OAuth2)
        if (!empty($bearerToken)) {
            if (strpos($bearerToken, 'Bearer ') !== 0) {
                return $this->unauthorizedResponse('Format Authorization header harus: Bearer {token}');
            }

            $token = str_replace('Bearer ', '', $bearerToken);
            $isValid = $this->validateBearerToken($token);

            if (!$isValid) {
                return $this->unauthorizedResponse('Token tidak valid atau telah expired.');
            }
        }

        // Rate limiting check
        $this->checkRateLimit($request);
    }

    /**
     * Validate API Key dari database/cache
     *
     * @param string $apiKey
     * @return bool
     */
    protected function validateApiKey(string $apiKey): bool
    {
        // Implementasi validasi API key
        // Bisa dari database, cache, atau environment variable
        $validKeys = config('Api')->keys ?? [];
        
        if (in_array($apiKey, $validKeys)) {
            return true;
        }

        // Check dari database jika ada model ApiKey
        try {
            $apiKeyModel = new \App\Models\ApiKeyModel();
            $key = $apiKeyModel->where('api_key', $apiKey)
                ->where('is_active', 1)
                ->first();
            
            return $key !== null;
        } catch (\Exception $e) {
            log_message('error', 'API Key validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate Bearer Token (JWT/OAuth2)
     *
     * @param string $token
     * @return bool
     */
    protected function validateBearerToken(string $token): bool
    {
        try {
            // Decode JWT token
            $firebaseJwt = new \Firebase\JWT\JWT();
            $decoded = $firebaseJwt::decode(
                $token,
                config('Encryption')->key,
                ['HS256']
            );

            // Check expiration
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Bearer token validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check rate limiting
     *
     * @param RequestInterface $request
     * @return void
     */
    protected function checkRateLimit(RequestInterface $request): void
    {
        $redis = service('redis');
        $clientIp = $request->getIPAddress();
        $apiKey = $request->getHeaderLine('X-API-Key');
        
        $identifier = $apiKey ?: $clientIp;
        $key = "api_rate_limit:{$identifier}";
        
        $limit = config('Api')->rateLimit ?? 60; // requests per minute
        $window = 60; // seconds

        $current = $redis->incr($key);
        
        if ($current === 1) {
            $redis->expire($key, $window);
        }

        if ($current > $limit) {
            $response = service('response');
            $response->setStatusCode(429);
            $response->setJSON([
                'success' => false,
                'message' => 'Rate limit exceeded. Maksimal ' . $limit . ' requests per menit.',
                'retry_after' => $redis->ttl($key),
            ]);
            $response->setHeader('Retry-After', (string) $redis->ttl($key));
            
            throw new \CodeIgniter\Exceptions\ServiceUnavailableException('Rate limit exceeded');
        }
    }

    /**
     * Return unauthorized JSON response
     *
     * @param string $message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function unauthorizedResponse(string $message): \CodeIgniter\HTTP\ResponseInterface
    {
        $response = service('response');
        $response->setStatusCode(401);
        $response->setJSON([
            'success' => false,
            'message' => $message,
            'code' => 'UNAUTHORIZED',
        ]);
        
        return $response;
    }

    /**
     * Allows After filters to inspect and modify the response
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Add CORS headers untuk API
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Authorization');
    }
}
