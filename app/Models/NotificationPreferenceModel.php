<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationPreferenceModel extends Model
{
    protected $table            = 'notification_preferences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'enable_inapp',
        'enable_email',
        'enable_whatsapp',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get preferences for a user
     */
    public function getForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Update preferences for a user
     */
    public function updatePreferences(int $userId, array $data): bool
    {
        $existing = $this->where('user_id', $userId)->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        $data['user_id'] = $userId;
        return $this->insert($data);
    }
}
