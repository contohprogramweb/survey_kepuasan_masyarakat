<?php

namespace App\Service;

use OTPHP\TOTP;
use App\Entity\User;

class MfaService
{
    private string $issuer;
    private int $digits;
    private int $period;

    public function __construct(
        string $issuer = 'MyApp',
        int $digits = 6,
        int $period = 30
    ) {
        $this->issuer = $issuer;
        $this->digits = $digits;
        $this->period = $period;
    }

    /**
     * Generate TOTP secret for a user
     */
    public function generateSecret(User $user): array
    {
        $secret = bin2hex(random_bytes(20)); // 160-bit secret
        
        $totp = TOTP::create($secret);
        $totp->setLabel($user->getUsername());
        $totp->setIssuer($this->issuer);
        $totp->setDigits($this->digits);
        $totp->setPeriod($this->period);

        return [
            'secret' => $secret,
            'uri' => $totp->getProvisioningUri(),
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qrcode/?size=300x300&data=' . urlencode($totp->getProvisioningUri()),
        ];
    }

    /**
     * Verify TOTP code
     */
    public function verifyCode(string $secret, string $code): bool
    {
        try {
            $totp = TOTP::create($secret);
            $totp->setDigits($this->digits);
            $totp->setPeriod($this->period);
            
            return $totp->now() === $code;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify TOTP code with window (allow for time drift)
     */
    public function verifyCodeWithWindow(string $secret, string $code, int $window = 1): bool
    {
        try {
            $totp = TOTP::create($secret);
            $totp->setDigits($this->digits);
            $totp->setPeriod($this->period);
            
            // Check current and adjacent time periods
            for ($i = -$window; $i <= $window; $i++) {
                if ($totp->at(new \DateTimeImmutable("+$i periods")) === $code) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enable MFA for a user
     */
    public function enableMfa(User $user, string $secret): User
    {
        return $user->enableMfa($secret);
    }

    /**
     * Disable MFA for a user
     */
    public function disableMfa(User $user): User
    {
        return $user->disableMfa();
    }

    /**
     * Check if MFA is required for user based on role
     */
    public function isMfaRequired(User $user): bool
    {
        return $user->isMfaRequired();
    }

    /**
     * Generate backup codes for MFA recovery
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(chunk_split(bin2hex(random_bytes(4)), 4, '-'), 0, -1));
        }
        return $codes;
    }

    /**
     * Validate backup code
     */
    public function validateBackupCode(array $backupCodes, string $code): bool
    {
        return in_array(strtoupper($code), $backupCodes, true);
    }
}
