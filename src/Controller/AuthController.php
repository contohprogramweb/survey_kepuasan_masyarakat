<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JwtService;
use App\Service\MfaService;
use App\Service\OAuth2\OAuth2Service;
use App\Service\Saml\SamlService;
use App\Service\Ldap\LdapService;
use App\Repository\UserRepository;

class AuthController
{
    private JwtService $jwtService;
    private MfaService $mfaService;
    private OAuth2Service $oauth2Service;
    private SamlService $samlService;
    private LdapService $ldapService;
    private UserRepository $userRepository;
    private int $sessionTimeout; // 30 minutes default

    public function __construct(
        JwtService $jwtService,
        MfaService $mfaService,
        OAuth2Service $oauth2Service,
        SamlService $samlService,
        LdapService $ldapService,
        UserRepository $userRepository,
        int $sessionTimeout = 1800
    ) {
        $this->jwtService = $jwtService;
        $this->mfaService = $mfaService;
        $this->oauth2Service = $oauth2Service;
        $this->samlService = $samlService;
        $this->ldapService = $ldapService;
        $this->userRepository = $userRepository;
        $this->sessionTimeout = $sessionTimeout;
    }

    /**
     * Display login page with method selection
     */
    public function showLoginPage(array $context = []): array
    {
        $availableMethods = [
            'local' => true,
            'oauth2' => !empty($this->oauth2Service->getAvailableProviders()),
            'saml' => true,
            'ldap' => LdapService::isAvailable(),
        ];

        $oauth2Providers = $this->oauth2Service->getAvailableProviders();

        return [
            'view' => 'login',
            'data' => [
                'available_methods' => $availableMethods,
                'oauth2_providers' => $oauth2Providers,
                'error' => $context['error'] ?? null,
                'success' => $context['success'] ?? null,
            ]
        ];
    }

    /**
     * Local login with username + password (bcrypt)
     */
    public function loginLocal(array $context): array
    {
        $username = $context['body']['username'] ?? '';
        $password = $context['body']['password'] ?? '';
        $mfaCode = $context['body']['mfa_code'] ?? null;

        if (empty($username) || empty($password)) {
            return $this->showLoginPage(['error' => 'Username and password are required']);
        }

        try {
            // Find user in database
            $user = $this->userRepository->findByUsername($username);
            
            if ($user === null) {
                // Use constant time comparison to prevent timing attacks
                hash_equals('dummy', $password);
                return $this->showLoginPage(['error' => 'Invalid credentials']);
            }

            // Verify password with bcrypt
            if (!password_verify($password, $user->getPasswordHash())) {
                return $this->showLoginPage(['error' => 'Invalid credentials']);
            }

            // Check if MFA is required for this user
            if ($user->isMfaRequired()) {
                if (!$user->isMfaEnabled()) {
                    return $this->showLoginPage([
                        'error' => 'MFA is required for your role. Please contact administrator.'
                    ]);
                }

                // If MFA code not provided, show MFA verification page
                if ($mfaCode === null) {
                    return $this->showMfaVerificationPage($user);
                }

                // Verify MFA code
                if (!$this->mfaService->verifyCodeWithWindow($user->getTotpSecret(), $mfaCode)) {
                    return $this->showMfaVerificationPage($user, 'Invalid MFA code');
                }
            }

            // Generate JWT tokens
            $tokens = $this->jwtService->generateTokens($user, true);

            // Update last login
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->userRepository->update($user);

            return [
                'view' => 'login_success',
                'data' => [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'expires_in' => $tokens['expires_in'],
                    'token_type' => $tokens['token_type'],
                    'user' => $user->toArray(),
                ]
            ];

        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return $this->showLoginPage(['error' => 'An error occurred during login']);
        }
    }

    /**
     * Show MFA verification page
     */
    private function showMfaVerificationPage(User $user, ?string $error = null): array
    {
        return [
            'view' => 'mfa_verify',
            'data' => [
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
                'error' => $error,
            ]
        ];
    }

    /**
     * Verify MFA code (second step of local login)
     */
    public function verifyMfa(array $context): array
    {
        $userId = $context['body']['user_id'] ?? null;
        $mfaCode = $context['body']['mfa_code'] ?? '';

        if ($userId === null || empty($mfaCode)) {
            return $this->showLoginPage(['error' => 'Invalid request']);
        }

        try {
            $user = $this->userRepository->findById($userId);
            
            if ($user === null || !$user->isMfaEnabled()) {
                return $this->showLoginPage(['error' => 'Invalid request']);
            }

            if (!$this->mfaService->verifyCodeWithWindow($user->getTotpSecret(), $mfaCode)) {
                return $this->showMfaVerificationPage($user, 'Invalid MFA code');
            }

            // Generate JWT tokens
            $tokens = $this->jwtService->generateTokens($user, true);

            // Update last login
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->userRepository->update($user);

            return [
                'view' => 'login_success',
                'data' => [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'expires_in' => $tokens['expires_in'],
                    'token_type' => $tokens['token_type'],
                    'user' => $user->toArray(),
                ]
            ];

        } catch (\Exception $e) {
            error_log('MFA verification error: ' . $e->getMessage());
            return $this->showLoginPage(['error' => 'An error occurred during MFA verification']);
        }
    }

    /**
     * OAuth2 login - Step 1: Redirect to provider
     */
    public function loginOAuth2(string $provider, array $context): array
    {
        if (!$this->oauth2Service->hasProvider($provider)) {
            return $this->showLoginPage(['error' => 'OAuth2 provider not configured']);
        }

        try {
            $authUrl = $this->oauth2Service->getAuthorizationUrl($provider);
            
            return [
                'redirect' => $authUrl,
            ];
        } catch (\Exception $e) {
            return $this->showLoginPage(['error' => 'Failed to initialize OAuth2 login: ' . $e->getMessage()]);
        }
    }

    /**
     * OAuth2 login - Step 2: Handle callback from provider
     */
    public function loginOAuth2Callback(array $context): array
    {
        $provider = $context['query']['provider'] ?? '';
        $code = $context['query']['code'] ?? '';

        if (empty($provider) || empty($code)) {
            return $this->showLoginPage(['error' => 'Invalid OAuth2 callback']);
        }

        try {
            $user = $this->oauth2Service->authenticate($provider, $code, function ($providerName, $userData, $token) {
                // Find or create user
                $user = $this->userRepository->findByEmail($userData['email']);
                
                if ($user === null) {
                    // Create new user
                    $user = $this->userRepository->create([
                        'username' => $userData['username'],
                        'email' => $userData['email'],
                        'password' => bin2hex(random_bytes(32)), // Random password
                        'roles' => ['Operator'], // Default role
                        'provider' => $providerName,
                        'metadata' => [
                            'oauth2_provider_id' => $userData['provider_id'],
                            'name' => $userData['name'] ?? null,
                            'avatar' => $userData['avatar'] ?? null,
                        ],
                    ]);
                } else {
                    // Update existing user metadata
                    $metadata = $user->getMetadata() ?? [];
                    $metadata['oauth2_provider_id'] = $userData['provider_id'];
                    $metadata['name'] = $userData['name'] ?? null;
                    $metadata['avatar'] = $userData['avatar'] ?? null;
                    $user = $this->userRepository->updateMetadata($user->getId(), $metadata);
                }

                return $user;
            });

            // Check MFA requirement
            if ($user->isMfaRequired() && !$user->isMfaEnabled()) {
                return $this->showLoginPage([
                    'error' => 'MFA is required for your role. Please contact administrator.'
                ]);
            }

            // Generate JWT tokens
            $tokens = $this->jwtService->generateTokens($user, !$user->isMfaRequired());

            // Update last login
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->userRepository->update($user);

            return [
                'view' => 'login_success',
                'data' => [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'expires_in' => $tokens['expires_in'],
                    'token_type' => $tokens['token_type'],
                    'user' => $user->toArray(),
                ]
            ];

        } catch (\Exception $e) {
            return $this->showLoginPage(['error' => 'OAuth2 authentication failed: ' . $e->getMessage()]);
        }
    }

    /**
     * SAML login - Step 1: Redirect to IdP
     */
    public function loginSaml(array $context): array
    {
        try {
            $returnTo = $context['base_url'] . '/auth/saml/callback';
            $authUrl = $this->samlService->login($returnTo);
            
            return [
                'redirect' => $authUrl,
            ];
        } catch (\Exception $e) {
            return $this->showLoginPage(['error' => 'SAML initialization failed: ' . $e->getMessage()]);
        }
    }

    /**
     * SAML login - Step 2: Handle response from IdP
     */
    public function loginSamlCallback(array $context): array
    {
        try {
            $user = $this->samlService->authenticate(function ($provider, $userData) {
                // Find or create user
                $user = $this->userRepository->findByEmail($userData['email']);
                
                if ($user === null) {
                    // Create new user with roles from SAML attributes
                    $user = $this->userRepository->create([
                        'username' => $userData['username'],
                        'email' => $userData['email'],
                        'password' => bin2hex(random_bytes(32)),
                        'roles' => !empty($userData['roles']) ? $userData['roles'] : ['Operator'],
                        'provider' => $provider,
                        'metadata' => [
                            'saml_nameid' => $userData['provider_id'],
                            'name' => $userData['name'] ?? null,
                            'first_name' => $userData['first_name'] ?? null,
                            'last_name' => $userData['last_name'] ?? null,
                            'saml_attributes' => $userData['attributes'],
                        ],
                    ]);
                } else {
                    // Update existing user
                    $metadata = $user->getMetadata() ?? [];
                    $metadata['saml_nameid'] = $userData['provider_id'];
                    $metadata['saml_attributes'] = $userData['attributes'];
                    
                    // Update roles from SAML if provided
                    $roles = !empty($userData['roles']) ? $userData['roles'] : $user->getRoles();
                    
                    $user = $this->userRepository->updateWithRoles($user->getId(), $metadata, $roles);
                }

                return $user;
            });

            // Check MFA requirement
            if ($user->isMfaRequired() && !$user->isMfaEnabled()) {
                return $this->showLoginPage([
                    'error' => 'MFA is required for your role. Please contact administrator.'
                ]);
            }

            // Generate JWT tokens
            $tokens = $this->jwtService->generateTokens($user, !$user->isMfaRequired());

            // Update last login
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->userRepository->update($user);

            return [
                'view' => 'login_success',
                'data' => [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'expires_in' => $tokens['expires_in'],
                    'token_type' => $tokens['token_type'],
                    'user' => $user->toArray(),
                ]
            ];

        } catch (\Exception $e) {
            return $this->showLoginPage(['error' => 'SAML authentication failed: ' . $e->getMessage()]);
        }
    }

    /**
     * LDAP login
     */
    public function loginLdap(array $context): array
    {
        $username = $context['body']['username'] ?? '';
        $password = $context['body']['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->showLoginPage(['error' => 'Username and password are required']);
        }

        if (!LdapService::isAvailable()) {
            return $this->showLoginPage(['error' => 'LDAP authentication is not available']);
        }

        try {
            $user = $this->ldapService->authenticate($username, $password);
            
            if ($user === null) {
                return $this->showLoginPage(['error' => 'Invalid credentials']);
            }

            // Sync user with local database
            $existingUser = $this->userRepository->findByUsername($username);
            
            if ($existingUser === null) {
                // Create user from LDAP data
                $user = $this->userRepository->create([
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'password' => '', // No password for LDAP users
                    'roles' => $user->getRoles(),
                    'provider' => 'ldap',
                    'metadata' => $user->getMetadata(),
                ]);
            } else {
                // Update roles and metadata from LDAP
                $user = $this->userRepository->updateWithRoles(
                    $existingUser->getId(),
                    $user->getMetadata(),
                    $user->getRoles()
                );
            }

            // Check MFA requirement
            if ($user->isMfaRequired() && !$user->isMfaEnabled()) {
                return $this->showLoginPage([
                    'error' => 'MFA is required for your role. Please contact administrator.'
                ]);
            }

            // Generate JWT tokens
            $tokens = $this->jwtService->generateTokens($user, !$user->isMfaRequired());

            // Update last login
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->userRepository->update($user);

            return [
                'view' => 'login_success',
                'data' => [
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'expires_in' => $tokens['expires_in'],
                    'token_type' => $tokens['token_type'],
                    'user' => $user->toArray(),
                ]
            ];

        } catch (\Exception $e) {
            error_log('LDAP login error: ' . $e->getMessage());
            return $this->showLoginPage(['error' => 'LDAP authentication failed']);
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(array $context): array
    {
        $refreshToken = $context['body']['refresh_token'] ?? 
                       $context['headers']['Authorization'] ?? 
                       null;

        if ($refreshToken === null) {
            return [
                'status' => 400,
                'error' => 'Refresh token required',
            ];
        }

        // Extract token from Bearer header if needed
        if (preg_match('/Bearer\s+(.+)/i', $refreshToken, $matches)) {
            $refreshToken = $matches[1];
        }

        try {
            $tokens = $this->jwtService->refreshAccessToken($refreshToken);

            return [
                'view' => 'token_refreshed',
                'data' => $tokens,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 401,
                'error' => 'Invalid or expired refresh token',
            ];
        }
    }

    /**
     * Logout user (revoke tokens)
     */
    public function logout(array $context): array
    {
        $refreshToken = $context['body']['refresh_token'] ?? null;

        if ($refreshToken !== null) {
            try {
                $claims = $this->jwtService->verifyRefreshToken($refreshToken);
                $userId = (int) $claims['sub'];
                $this->jwtService->revokeAllUserTokens($userId);
            } catch (\Exception $e) {
                // Token might be expired, continue with logout
            }
        }

        return [
            'view' => 'logout_success',
            'data' => [
                'message' => 'Successfully logged out',
            ]
        ];
    }

    /**
     * Setup MFA for user (generate QR code)
     */
    public function setupMfa(array $context): array
    {
        $userId = $context['user']->getId() ?? null;
        
        if ($userId === null) {
            return [
                'status' => 401,
                'error' => 'Authentication required',
            ];
        }

        try {
            $user = $this->userRepository->findById($userId);
            
            if ($user === null) {
                return [
                    'status' => 404,
                    'error' => 'User not found',
                ];
            }

            $mfaData = $this->mfaService->generateSecret($user);
            $backupCodes = $this->mfaService->generateBackupCodes();

            return [
                'view' => 'mfa_setup',
                'data' => [
                    'qr_code_url' => $mfaData['qr_code_url'],
                    'secret' => $mfaData['secret'],
                    'uri' => $mfaData['uri'],
                    'backup_codes' => $backupCodes,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'error' => 'Failed to setup MFA',
            ];
        }
    }

    /**
     * Enable MFA for user after verification
     */
    public function enableMfa(array $context): array
    {
        $userId = $context['user']->getId() ?? null;
        $code = $context['body']['code'] ?? '';
        $secret = $context['body']['secret'] ?? '';

        if ($userId === null || empty($code) || empty($secret)) {
            return [
                'status' => 400,
                'error' => 'Invalid request',
            ];
        }

        try {
            $user = $this->userRepository->findById($userId);
            
            if ($user === null) {
                return [
                    'status' => 404,
                    'error' => 'User not found',
                ];
            }

            if (!$this->mfaService->verifyCode($secret, $code)) {
                return [
                    'status' => 400,
                    'error' => 'Invalid verification code',
                ];
            }

            // Enable MFA for user
            $user = $this->mfaService->enableMfa($user, $secret);
            $this->userRepository->update($user);

            return [
                'view' => 'mfa_enabled',
                'data' => [
                    'message' => 'MFA enabled successfully',
                    'mfa_enabled' => true,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'error' => 'Failed to enable MFA',
            ];
        }
    }

    /**
     * Get current session info
     */
    public function getSessionInfo(array $context): array
    {
        $user = $context['user'] ?? null;
        
        if ($user === null) {
            return [
                'status' => 401,
                'error' => 'Not authenticated',
            ];
        }

        return [
            'view' => 'session_info',
            'data' => [
                'user' => $user->toArray(),
                'mfa_required' => $user->isMfaRequired(),
                'mfa_enabled' => $user->isMfaEnabled(),
                'session_timeout' => $this->sessionTimeout,
            ]
        ];
    }
}
