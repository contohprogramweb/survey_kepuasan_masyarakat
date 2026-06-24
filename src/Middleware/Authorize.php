<?php

namespace App\Middleware;

use App\Entity\User;

class Authorize
{
    private array $requiredRoles;
    private array $requiredPermissions;
    private bool $requireAllRoles;
    private bool $requireAllPermissions;

    /**
     * @param array|string $roles Role or array of roles required
     * @param array|string $permissions Permission or array of permissions required
     * @param bool $requireAll Whether all roles/permissions are required (AND) or any (OR)
     */
    public function __construct(
        array|string $roles = [],
        array|string $permissions = [],
        bool $requireAllRoles = false,
        bool $requireAllPermissions = false
    ) {
        $this->requiredRoles = is_array($roles) ? $roles : [$roles];
        $this->requiredPermissions = is_array($permissions) ? $permissions : [$permissions];
        $this->requireAllRoles = $requireAllRoles;
        $this->requireAllPermissions = $requireAllPermissions;
    }

    /**
     * Handle authorization middleware
     */
    public function handle(callable $next, array $context = []): mixed
    {
        /** @var User|null $user */
        $user = $context['user'] ?? null;

        if ($user === null) {
            throw new \UnauthorizedException('Authentication required');
        }

        // Check roles
        if (!empty($this->requiredRoles)) {
            if (!$this->checkRoles($user)) {
                throw new \ForbiddenException(
                    'Access denied. Required role(s): ' . implode(', ', $this->requiredRoles)
                );
            }
        }

        // Check permissions
        if (!empty($this->requiredPermissions)) {
            if (!$this->checkPermissions($user)) {
                throw new \ForbiddenException(
                    'Access denied. Required permission(s): ' . implode(', ', $this->requiredPermissions)
                );
            }
        }

        return $next($context);
    }

    /**
     * Check if user has required roles
     */
    private function checkRoles(User $user): bool
    {
        if ($this->requireAllRoles) {
            // User must have ALL specified roles
            return $user->hasAllRoles($this->requiredRoles);
        } else {
            // User must have ANY of the specified roles
            foreach ($this->requiredRoles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Check if user has required permissions
     */
    private function checkPermissions(User $user): bool
    {
        if ($this->requireAllPermissions) {
            // User must have ALL specified permissions
            foreach ($this->requiredPermissions as $permission) {
                if (!$user->hasPermission($permission)) {
                    return false;
                }
            }
            return true;
        } else {
            // User must have ANY of the specified permissions
            foreach ($this->requiredPermissions as $permission) {
                if ($user->hasPermission($permission)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Create middleware for specific role
     */
    public static function role(string $role): self
    {
        return new self($role);
    }

    /**
     * Create middleware for any of the specified roles
     */
    public static function anyRole(array $roles): self
    {
        return new self($roles, [], false);
    }

    /**
     * Create middleware for all of the specified roles
     */
    public static function allRoles(array $roles): self
    {
        return new self($roles, [], true);
    }

    /**
     * Create middleware for specific permission
     */
    public static function permission(string $permission): self
    {
        return new self([], $permission);
    }

    /**
     * Create middleware for any of the specified permissions
     */
    public static function anyPermission(array $permissions): self
    {
        return new self([], $permissions, false, false);
    }

    /**
     * Create middleware for all of the specified permissions
     */
    public static function allPermissions(array $permissions): self
    {
        return new self([], $permissions, false, true);
    }

    /**
     * Create middleware for Super Admin role only
     */
    public static function superAdmin(): self
    {
        return new self('Super Admin');
    }

    /**
     * Create middleware for Admin role
     */
    public static function admin(): self
    {
        return new self('Admin');
    }

    /**
     * Create middleware for DPO role
     */
    public static function dpo(): self
    {
        return new self('DPO');
    }

    /**
     * Create middleware for DevOps role
     */
    public static function devops(): self
    {
        return new self('DevOps');
    }

    /**
     * Create middleware for Pimpinan role
     */
    public static function pimpinan(): self
    {
        return new self('Pimpinan');
    }

    /**
     * Create middleware for Operator role
     */
    public static function operator(): self
    {
        return new self('Operator');
    }
}
