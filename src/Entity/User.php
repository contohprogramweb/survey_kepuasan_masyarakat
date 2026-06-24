<?php

namespace App\Entity;

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private array $roles;
    private ?string $totpSecret;
    private bool $mfaEnabled;
    private ?string $provider;
    private ?array $metadata;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $lastLoginAt;

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $passwordHash,
        array $roles = [],
        ?string $totpSecret = null,
        bool $mfaEnabled = false,
        ?string $provider = 'local',
        ?array $metadata = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
        $this->totpSecret = $totpSecret;
        $this->mfaEnabled = $mfaEnabled;
        $this->provider = $provider;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->roles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the specified roles
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!in_array($role, $this->roles, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has a specific permission based on role
     * Role hierarchy: Super Admin > Admin > Operator > Pimpinan > DPO > DevOps
     */
    public function hasPermission(string $permission): bool
    {
        $rolePermissions = [
            'Super Admin' => ['*'], // All permissions
            'Admin' => ['user.read', 'user.write', 'user.delete', 'system.read', 'system.write', 'audit.read'],
            'Operator' => ['user.read', 'user.write', 'system.read'],
            'Pimpinan' => ['user.read', 'report.read', 'report.write', 'audit.read'],
            'DPO' => ['user.read', 'data.read', 'data.write', 'data.delete', 'audit.read', 'privacy.read', 'privacy.write'],
            'DevOps' => ['system.read', 'system.write', 'deploy.read', 'deploy.write', 'monitoring.read'],
        ];

        foreach ($this->roles as $role) {
            if (!isset($rolePermissions[$role])) {
                continue;
            }

            $permissions = $rolePermissions[$role];
            
            // Super Admin has all permissions
            if (in_array('*', $permissions, true)) {
                return true;
            }

            if (in_array($permission, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if MFA is required for this user
     * MFA is mandatory for Super Admin and DPO
     */
    public function isMfaRequired(): bool
    {
        return $this->hasRole('Super Admin') || $this->hasRole('DPO');
    }

    /**
     * Enable MFA with TOTP secret
     */
    public function enableMfa(string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;
        $this->mfaEnabled = true;
        return $this;
    }

    /**
     * Disable MFA
     */
    public function disableMfa(): self
    {
        $this->totpSecret = null;
        $this->mfaEnabled = false;
        return $this;
    }

    /**
     * Add a role to the user
     */
    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Remove a role from the user
     */
    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles, true);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
        return $this;
    }

    /**
     * Convert user to array for JWT claims
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles,
            'mfa_enabled' => $this->mfaEnabled,
            'provider' => $this->provider,
        ];
    }
}
