<?php

namespace App\Repository;

use App\Entity\User;

class UserRepository
{
    private array $users = [];

    public function __construct()
    {
        // Initialize with some sample users for testing
        $this->initializeSampleUsers();
    }

    /**
     * Initialize sample users for demonstration
     */
    private function initializeSampleUsers(): void
    {
        // Super Admin user (MFA required)
        $superAdmin = new User(
            1,
            'superadmin',
            'superadmin@example.com',
            password_hash('SuperAdmin123!', PASSWORD_BCRYPT),
            ['Super Admin'],
            null,
            false,
            'local'
        );
        $this->users[1] = $superAdmin;

        // Admin user
        $admin = new User(
            2,
            'admin',
            'admin@example.com',
            password_hash('Admin123!', PASSWORD_BCRYPT),
            ['Admin'],
            null,
            false,
            'local'
        );
        $this->users[2] = $admin;

        // Operator user
        $operator = new User(
            3,
            'operator',
            'operator@example.com',
            password_hash('Operator123!', PASSWORD_BCRYPT),
            ['Operator'],
            null,
            false,
            'local'
        );
        $this->users[3] = $operator;

        // Pimpinan user
        $pimpinan = new User(
            4,
            'pimpinan',
            'pimpinan@example.com',
            password_hash('Pimpinan123!', PASSWORD_BCRYPT),
            ['Pimpinan'],
            null,
            false,
            'local'
        );
        $this->users[4] = $pimpinan;

        // DPO user (MFA required)
        $dpo = new User(
            5,
            'dpo',
            'dpo@example.com',
            password_hash('DPO123!', PASSWORD_BCRYPT),
            ['DPO'],
            null,
            false,
            'local'
        );
        $this->users[5] = $dpo;

        // DevOps user
        $devops = new User(
            6,
            'devops',
            'devops@example.com',
            password_hash('DevOps123!', PASSWORD_BCRYPT),
            ['DevOps'],
            null,
            false,
            'local'
        );
        $this->users[6] = $devops;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getUsername() === $username) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Find user by provider and provider ID
     */
    public function findByProvider(string $provider, string $providerId): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getProvider() === $provider) {
                $metadata = $user->getMetadata();
                if ($metadata !== null) {
                    if (($metadata['oauth2_provider_id'] ?? '') === $providerId ||
                        ($metadata['saml_nameid'] ?? '') === $providerId) {
                        return $user;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        $id = max(array_keys($this->users)) + 1;
        
        $user = new User(
            $id,
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['roles'] ?? [],
            null,
            false,
            $data['provider'] ?? 'local',
            $data['metadata'] ?? null
        );

        $this->users[$id] = $user;
        return $user;
    }

    /**
     * Update user
     */
    public function update(User $user): User
    {
        $this->users[$user->getId()] = $user;
        return $user;
    }

    /**
     * Update user metadata
     */
    public function updateMetadata(int $userId, array $metadata): User
    {
        $user = $this->findById($userId);
        
        if ($user === null) {
            throw new \RuntimeException("User $userId not found");
        }

        $existingMetadata = $user->getMetadata() ?? [];
        $mergedMetadata = array_merge($existingMetadata, $metadata);

        // Use reflection to update private property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('metadata');
        $property->setAccessible(true);
        $property->setValue($user, $mergedMetadata);

        return $user;
    }

    /**
     * Update user with roles
     */
    public function updateWithRoles(int $userId, array $metadata, array $roles): User
    {
        $user = $this->findById($userId);
        
        if ($user === null) {
            throw new \RuntimeException("User $userId not found");
        }

        // Update metadata
        if (!empty($metadata)) {
            $this->updateMetadata($userId, $metadata);
        }

        // Update roles using reflection
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, $roles);

        return $user;
    }

    /**
     * Delete user
     */
    public function delete(int $userId): bool
    {
        if (isset($this->users[$userId])) {
            unset($this->users[$userId]);
            return true;
        }
        return false;
    }

    /**
     * Get all users
     */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    /**
     * Find users by role
     */
    public function findByRole(string $role): array
    {
        $result = [];
        foreach ($this->users as $user) {
            if ($user->hasRole($role)) {
                $result[] = $user;
            }
        }
        return $result;
    }

    /**
     * Count users
     */
    public function count(): int
    {
        return count($this->users);
    }
}
