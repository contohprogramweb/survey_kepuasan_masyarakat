<?php

namespace App\Model;

/**
 * User Model dengan Soft Delete
 * 
 * Berdasarkan SRS F-01 dan UC-02
 * Fitur: CRUD, Role Assignment, Password Reset, Aktivasi/Deaktivasi
 */
class UserModel
{
    private \PDO $db;
    private array $userData = [];
    
    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    
    // Roles
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_OPERATOR = 'operator';
    public const ROLE_PIMPINAN = 'pimpinan';
    public const ROLE_DPO = 'dpo';
    public const ROLE_DEVOPS = 'devops';
    
    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_OPERATOR => 'Operator',
        self::ROLE_PIMPINAN => 'Pimpinan',
        self::ROLE_DPO => 'DPO',
        self::ROLE_DEVOPS => 'DevOps',
    ];
    
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Load user data by ID
     */
    public function loadById(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $this->userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return !empty($this->userData);
    }
    
    /**
     * Load user by username
     */
    public function loadByUsername(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL");
        $stmt->execute([$username]);
        $this->userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return !empty($this->userData);
    }
    
    /**
     * Load user by email
     */
    public function loadByEmail(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $this->userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return !empty($this->userData);
    }
    
    /**
     * Check if username exists (excluding current user)
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ? AND deleted_at IS NULL";
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
        }
        
        $stmt = $this->db->prepare($sql);
        if ($excludeId !== null) {
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt->execute([$username]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if email exists (excluding current user)
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ? AND deleted_at IS NULL";
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
        }
        
        $stmt = $this->db->prepare($sql);
        if ($excludeId !== null) {
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt->execute([$email]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Create new user
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                username, email, password_hash, full_name, role, 
                status, mfa_enabled, mfa_secret, created_by, updated_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['role'] ?? self::ROLE_OPERATOR,
            $data['status'] ?? self::STATUS_ACTIVE,
            $data['mfa_enabled'] ?? false,
            $data['mfa_secret'] ?? null,
            $data['created_by'],
            $data['created_by']
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        $allowedFields = ['email', 'full_name', 'role', 'status', 'mfa_enabled', 'mfa_secret'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Add updated_at and updated_by
        $fields[] = "updated_at = NOW()";
        $fields[] = "updated_by = ?";
        $values[] = $data['updated_by'];
        
        $values[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Soft delete user (cannot delete Super Admin)
     */
    public function softDelete(int $id, int $deletedBy): bool
    {
        // Prevent deletion of Super Admin
        if ($this->isSuperAdmin($id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET deleted_at = NOW(), deleted_by = ?, updated_by = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$deletedBy, $deletedBy, $id]);
    }
    
    /**
     * Hard delete (only for non-Super Admin, use with caution)
     */
    public function hardDelete(int $id): bool
    {
        if ($this->isSuperAdmin($id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Reset password
     */
    public function resetPassword(int $id, string $newPasswordHash, int $resetBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_hash = ?, password_reset_at = NOW(), updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$newPasswordHash, $resetBy, $id]);
    }
    
    /**
     * Activate user account
     */
    public function activate(int $id, int $activatedBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = ?, activated_at = NOW(), updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([self::STATUS_ACTIVE, $activatedBy, $id]);
    }
    
    /**
     * Deactivate user account (cannot deactivate Super Admin)
     */
    public function deactivate(int $id, int $deactivatedBy): bool
    {
        if ($this->isSuperAdmin($id)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = ?, deactivated_at = NOW(), updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([self::STATUS_INACTIVE, $deactivatedBy, $id]);
    }
    
    /**
     * Suspend user account
     */
    public function suspend(int $id, string $reason, int $suspendedBy): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = ?, suspension_reason = ?, suspended_at = NOW(), updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([self::STATUS_SUSPENDED, $reason, $suspendedBy, $id]);
    }
    
    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(?int $id = null): bool
    {
        $userId = $id ?? $this->getId();
        return $userId && $this->getRole($userId) === self::ROLE_SUPER_ADMIN;
    }
    
    /**
     * Get user data
     */
    public function getData(): array
    {
        return $this->userData;
    }
    
    /**
     * Get user ID
     */
    public function getId(): ?int
    {
        return isset($this->userData['id']) ? (int) $this->userData['id'] : null;
    }
    
    /**
     * Get user role
     */
    public function getRole(?int $userId = null): ?string
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['role'] ?? null;
        }
        
        return $this->userData['role'] ?? null;
    }
    
    /**
     * Get user status
     */
    public function getStatus(): ?string
    {
        return $this->userData['status'] ?? null;
    }
    
    /**
     * Get all users for DataTables server-side processing
     */
    public function getUsersForDataTable(
        int $draw,
        int $start,
        int $length,
        array $order,
        array $search,
        ?string $roleFilter = null,
        ?string $statusFilter = null
    ): array {
        // Build WHERE clause
        $whereConditions = ['u.deleted_at IS NULL'];
        $params = [];
        
        // Role filter
        if ($roleFilter && in_array($roleFilter, array_keys(self::ROLES))) {
            $whereConditions[] = "u.role = ?";
            $params[] = $roleFilter;
        }
        
        // Status filter
        if ($statusFilter && in_array($statusFilter, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUSPENDED])) {
            $whereConditions[] = "u.status = ?";
            $params[] = $statusFilter;
        }
        
        // Search filter
        if (!empty($search['value'])) {
            $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
            $searchTerm = "%{$search['value']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Ordering
        $orderColumns = ['u.id', 'u.username', 'u.email', 'u.full_name', 'u.role', 'u.status', 'u.created_at'];
        $orderColumn = $orderColumns[$order['column'] ?? 0] ?? 'u.id';
        $orderDir = strtoupper($order['dir'] ?? 'ASC');
        $orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'ASC';
        
        // Get total count
        $totalStmt = $this->db->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL");
        $totalRecords = (int) $totalStmt->fetchColumn();
        
        // Get filtered count
        $filterStmt = $this->db->prepare("SELECT COUNT(*) FROM users u WHERE $whereClause");
        $filterStmt->execute($params);
        $filteredRecords = (int) $filterStmt->fetchColumn();
        
        // Get data
        $limit = $length > 0 ? $length : 10;
        $sql = "
            SELECT u.*, 
                   creator.full_name as created_by_name,
                   updater.full_name as updated_by_name
            FROM users u
            LEFT JOIN users creator ON u.created_by = creator.id
            LEFT JOIN users updater ON u.updated_by = updater.id
            WHERE $whereClause
            ORDER BY $orderColumn $orderDir
            LIMIT $start, $limit
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $users,
        ];
    }
    
    /**
     * Get all active users (for dropdowns, etc.)
     */
    public function getActiveUsers(?string $excludeRole = null): array
    {
        $sql = "SELECT id, username, full_name, role FROM users 
                WHERE status = ? AND deleted_at IS NULL";
        $params = [self::STATUS_ACTIVE];
        
        if ($excludeRole) {
            $sql .= " AND role != ?";
            $params[] = $excludeRole;
        }
        
        $sql .= " ORDER BY full_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
