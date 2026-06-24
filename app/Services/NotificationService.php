<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\NotificationPreferenceModel;
use App\Jobs\EmailJob;
use App\Jobs\WhatsAppJob;
use CodeIgniter\Database\BaseConnection;

class NotificationService
{
    protected $notificationModel;
    protected $preferenceModel;
    protected $db;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->preferenceModel = new NotificationPreferenceModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Send notification through available channels based on user preferences
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type (info, warning, danger, success)
     * @param array $data (additional data like survey_id, url)
     * @return bool
     */
    public function send(int $userId, string $title, string $message, string $type = 'info', array $data = []): bool
    {
        $preferences = $this->preferenceModel->getForUser($userId);
        
        // Default preferences if not set
        if (!$preferences) {
            $preferences = [
                'enable_inapp' => 1,
                'enable_email' => 1,
                'enable_whatsapp' => 0,
            ];
        }

        $success = true;

        // In-App Notification (always store in DB)
        if ($preferences['enable_inapp']) {
            $notifId = $this->createNotification($userId, $title, $message, $type, 'inapp', $data);
            if (!$notifId) {
                $success = false;
            }
        }

        // Email Notification
        if ($preferences['enable_email']) {
            $notifId = $this->createNotification($userId, $title, $message, $type, 'email', $data);
            if ($notifId) {
                // Queue email job
                $this->queueEmailJob($notifId, $userId, $title, $message, $data);
            } else {
                $success = false;
            }
        }

        // WhatsApp Notification
        if ($preferences['enable_whatsapp']) {
            $notifId = $this->createNotification($userId, $title, $message, $type, 'whatsapp', $data);
            if ($notifId) {
                // Queue WhatsApp job
                $this->queueWhatsAppJob($notifId, $userId, $title, $message, $data);
            } else {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Create notification record in database
     */
    protected function createNotification(int $userId, string $title, string $message, string $type, string $channel, array $data): ?int
    {
        $notifData = [
            'user_id'   => $userId,
            'title'     => $title,
            'message'   => $message,
            'type'      => $type,
            'channel'   => $channel,
            'is_read'   => 0,
            'data'      => json_encode($data),
            'sent_at'   => null,
        ];

        $id = $this->notificationModel->insert($notifData);
        return $id ? (int)$id : null;
    }

    /**
     * Queue email job for async processing
     */
    protected function queueEmailJob(int $notifId, int $userId, string $title, string $message, array $data): void
    {
        // In production, use a real queue system (Redis, Beanstalkd, etc.)
        // For now, we simulate by creating a job record or calling directly
        $job = new EmailJob();
        $job->handle($notifId, $userId, $title, $message, $data);
        
        // Mark as sent in DB
        $this->notificationModel->update($notifId, ['sent_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Queue WhatsApp job for async processing
     */
    protected function queueWhatsAppJob(int $notifId, int $userId, string $title, string $message, array $data): void
    {
        // In production, use a real queue system
        $job = new WhatsAppJob();
        $job->handle($notifId, $userId, $title, $message, $data);
        
        // Mark as sent in DB
        $this->notificationModel->update($notifId, ['sent_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Get latest notifications
     */
    public function getLatest(int $userId, int $limit = 10): array
    {
        return $this->notificationModel->getLatest($userId, $limit);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id, int $userId): bool
    {
        return $this->notificationModel->markAsRead($id, $userId);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->notificationModel->markAllAsRead($userId);
    }

    /**
     * Get user preferences
     */
    public function getPreferences(int $userId): ?array
    {
        return $this->preferenceModel->getForUser($userId);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(int $userId, array $data): bool
    {
        return $this->preferenceModel->updatePreferences($userId, $data);
    }
}
