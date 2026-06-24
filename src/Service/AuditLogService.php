<?php

namespace App\Service;

/**
 * Audit Log Service
 * Mencatat semua aksi CRUD pada user management
 */
class AuditLogService
{
    private \PDO $db;
    
    // Action types
    public const ACTION_CREATE = 'CREATE';
    public const ACTION_UPDATE = 'UPDATE';
    public const ACTION_DELETE = 'DELETE';
    public const ACTION_SOFT_DELETE = 'SOFT_DELETE';
    public const ACTION_ACTIVATE = 'ACTIVATE';
    public const ACTION_DEACTIVATE = 'DEACTIVATE';
    public const ACTION_SUSPEND = 'SUSPEND';
    public const ACTION_PASSWORD_RESET = 'PASSWORD_RESET';
    public const ACTION_ROLE_CHANGE = 'ROLE_CHANGE';
    public const ACTION_LOGIN = 'LOGIN';
    public const ACTION_LOGOUT = 'LOGOUT';
    
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Log an action
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $oldValues = [],
        array $newValues = [],
        string $ipAddress = '',
        string $userAgent = ''
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (
                user_id, action, entity_type, entity_id,
                old_values, new_values, ip_address, user_agent,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            !empty($oldValues) ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            !empty($newValues) ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            $ipAddress ?: $this->getClientIp(),
            $userAgent ?: $this->getUserAgent()
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Log user creation
     */
    public function logUserCreate(int $actorId, int $newUserId, array $userData): void
    {
        $this->log(
            $actorId,
            self::ACTION_CREATE,
            'user',
            $newUserId,
            [],
            $this->sanitizeForLog($userData, ['password_hash', 'mfa_secret'])
        );
    }
    
    /**
     * Log user update
     */
    public function logUserUpdate(int $actorId, int $userId, array $oldData, array $newData): void
    {
        $this->log(
            $actorId,
            self::ACTION_UPDATE,
            'user',
            $userId,
            $this->sanitizeForLog($oldData),
            $this->sanitizeForLog($newData)
        );
    }
    
    /**
     * Log soft delete
     */
    public function logSoftDelete(int $actorId, int $userId): void
    {
        $this->log(
            $actorId,
            self::ACTION_SOFT_DELETE,
            'user',
            $userId
        );
    }
    
    /**
     * Log password reset
     */
    public function logPasswordReset(int $actorId, int $userId): void
    {
        $this->log(
            $actorId,
            self::ACTION_PASSWORD_RESET,
            'user',
            $userId
        );
    }
    
    /**
     * Log role change
     */
    public function logRoleChange(int $actorId, int $userId, string $oldRole, string $newRole): void
    {
        $this->log(
            $actorId,
            self::ACTION_ROLE_CHANGE,
            'user',
            $userId,
            ['role' => $oldRole],
            ['role' => $newRole]
        );
    }
    
    /**
     * Log activation
     */
    public function logActivate(int $actorId, int $userId): void
    {
        $this->log(
            $actorId,
            self::ACTION_ACTIVATE,
            'user',
            $userId
        );
    }
    
    /**
     * Log deactivation
     */
    public function logDeactivate(int $actorId, int $userId): void
    {
        $this->log(
            $actorId,
            self::ACTION_DEACTIVATE,
            'user',
            $userId
        );
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    /**
     * Sanitize data for logging (remove sensitive fields)
     */
    private function sanitizeForLog(array $data, array $excludeFields = []): array
    {
        $sensitiveFields = ['password_hash', 'mfa_secret', 'password', 'token'];
        $excludeFields = array_merge($sensitiveFields, $excludeFields);
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $excludeFields)) {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get audit logs for an entity
     */
    public function getLogsForEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT al.*, u.username as actor_username, u.full_name as actor_name
            FROM audit_log al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity_type = ? AND al.entity_id = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$entityType, $entityId, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
