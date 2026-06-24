<?php

namespace App\Middleware;

use App\Service\JwtService;
use App\Entity\User;

class Authenticate
{
    private JwtService $jwtService;
    private ?User $currentUser = null;
    private array $currentClaims = [];

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle authentication middleware
     */
    public function handle(callable $next, array $context = []): mixed
    {
        // Get token from request (supports multiple locations)
        $token = $this->extractToken($context);

        if ($token === null) {
            throw new \UnauthorizedException('Authentication required. No token provided.');
        }

        try {
            // Verify and decode the token
            $claims = $this->jwtService->verifyAccessToken($token);
            
            // Store claims for later use
            $this->currentClaims = $claims;

            // Create user object from claims (in real app, fetch from database)
            $this->currentUser = $this->createUserFromClaims($claims);

            // Add user to context
            $context['user'] = $this->currentUser;
            $context['claims'] = $claims;

        } catch (\Exception $e) {
            throw new \UnauthorizedException('Invalid or expired token: ' . $e->getMessage());
        }

        return $next($context);
    }

    /**
     * Extract token from request context
     * Supports: Authorization header, cookie, query parameter
     */
    private function extractToken(array $context): ?string
    {
        // Try Authorization header first (Bearer token)
        if (isset($context['headers']['Authorization'])) {
            $authHeader = $context['headers']['Authorization'];
            if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        // Try cookie
        if (isset($context['cookies']['access_token'])) {
            return $context['cookies']['access_token'];
        }

        // Try query parameter (less secure, but useful for some cases)
        if (isset($context['query']['token'])) {
            return $context['query']['token'];
        }

        // Try request body
        if (isset($context['body']['access_token'])) {
            return $context['body']['access_token'];
        }

        return null;
    }

    /**
     * Create User entity from JWT claims
     * In production, this should fetch from database
     */
    private function createUserFromClaims(array $claims): User
    {
        return new User(
            (int) $claims['sub'],
            $claims['username'],
            $claims['email'],
            '', // Password hash not needed for authenticated user
            $claims['roles'] ?? [],
            null, // TOTP secret not in token
            $claims['mfa_verified'] ?? false,
            'local', // Provider - would need to be stored in token or DB
            $claims // Store all claims as metadata
        );
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * Get current JWT claims
     */
    public function getCurrentClaims(): array
    {
        return $this->currentClaims;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }

    /**
     * Get user ID from token
     */
    public function getUserId(): ?int
    {
        return $this->currentUser?->getId();
    }

    /**
     * Logout user (revoke tokens)
     */
    public function logout(string $refreshToken = null): void
    {
        if ($this->currentUser !== null) {
            // Revoke all user tokens
            $this->jwtService->revokeAllUserTokens($this->currentUser->getId());
            
            // Clear current user
            $this->currentUser = null;
            $this->currentClaims = [];
        }
    }
}
