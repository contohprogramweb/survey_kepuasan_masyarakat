<?php

namespace App\Middleware;

use App\Entity\User;
use App\Service\MfaService;

class MfaRequired
{
    private MfaService $mfaService;
    private bool $strict;

    /**
     * @param bool $strict If true, deny access when MFA not verified. If false, allow redirect to MFA setup
     */
    public function __construct(MfaService $mfaService, bool $strict = true)
    {
        $this->mfaService = $mfaService;
        $this->strict = $strict;
    }

    /**
     * Handle MFA verification middleware
     */
    public function handle(callable $next, array $context = []): mixed
    {
        /** @var User|null $user */
        $user = $context['user'] ?? null;

        if ($user === null) {
            throw new \UnauthorizedException('Authentication required');
        }

        // Check if MFA is required for this user based on role
        if (!$this->mfaService->isMfaRequired($user)) {
            // MFA not required for this user, proceed
            return $next($context);
        }

        // MFA is required - check if it's enabled
        if (!$user->isMfaEnabled()) {
            if ($this->strict) {
                throw new \ForbiddenException(
                    'MFA is required for your role but not enabled. Please contact administrator.'
                );
            } else {
                // Redirect to MFA setup
                $context['redirect_to_mfa_setup'] = true;
                return $next($context);
            }
        }

        // Check if MFA has been verified in current session
        $mfaVerified = $context['mfa_verified'] ?? false;
        
        if (!$mfaVerified) {
            // MFA not yet verified, require verification
            if ($this->strict) {
                throw new \MfaRequiredException(
                    'Multi-factor authentication verification required',
                    [
                        'user_id' => $user->getId(),
                        'username' => $user->getUsername(),
                        'mfa_enabled' => $user->isMfaEnabled(),
                    ]
                );
            } else {
                // Redirect to MFA verification page
                $context['redirect_to_mfa_verify'] = true;
                return $next($context);
            }
        }

        // MFA verified, proceed
        return $next($context);
    }

    /**
     * Verify MFA code from context
     */
    public function verifyMfaCode(User $user, string $code, array &$context): bool
    {
        if (!$user->isMfaEnabled()) {
            return false;
        }

        $totpSecret = $user->getTotpSecret();
        if ($totpSecret === null) {
            return false;
        }

        // Verify with time window to account for clock drift
        $isValid = $this->mfaService->verifyCodeWithWindow($totpSecret, $code, 1);

        if ($isValid) {
            $context['mfa_verified'] = true;
            $context['mfa_verified_at'] = new \DateTimeImmutable();
        }

        return $isValid;
    }

    /**
     * Check if MFA verification is present in context
     */
    public function isMfaVerified(array $context): bool
    {
        return $context['mfa_verified'] ?? false;
    }

    /**
     * Get MFA verification timestamp from context
     */
    public function getMfaVerifiedAt(array $context): ?\DateTimeImmutable
    {
        return $context['mfa_verified_at'] ?? null;
    }

    /**
     * Check if MFA verification has expired (e.g., after 8 hours)
     */
    public function isMfaVerificationExpired(array $context, int $maxAgeSeconds = 28800): bool
    {
        $verifiedAt = $this->getMfaVerifiedAt($context);
        
        if ($verifiedAt === null) {
            return true;
        }

        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $verifiedAt->getTimestamp();

        return $diff > $maxAgeSeconds;
    }

    /**
     * Create strict MFA middleware (deny access without MFA)
     */
    public static function strict(MfaService $mfaService): self
    {
        return new self($mfaService, true);
    }

    /**
     * Create lenient MFA middleware (allow redirect to MFA setup)
     */
    public static function lenient(MfaService $mfaService): self
    {
        return new self($mfaService, false);
    }
}
