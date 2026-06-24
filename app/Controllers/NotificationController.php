<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\NotificationService;

class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        helper('json');
    }

    /**
     * Get unread count (for badge in header)
     * AJAX endpoint
     */
    public function unreadCount()
    {
        $userId = user_id(); // Assuming auth helper exists
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $count = $this->notificationService->getUnreadCount($userId);
        return $this->response->setJSON(['count' => $count]);
    }

    /**
     * Get latest notifications (for dropdown list)
     * AJAX endpoint
     */
    public function getList()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $limit = $this->request->getGet('limit') ?? 10;
        $notifications = $this->notificationService->getLatest($userId, (int)$limit);

        // Format for frontend
        $formatted = [];
        foreach ($notifications as $notif) {
            $formatted[] = [
                'id'        => $notif['id'],
                'title'     => $notif['title'],
                'message'   => $notif['message'],
                'type'      => $notif['type'],
                'is_read'   => (bool)$notif['is_read'],
                'created_at'=> $notif['created_at'],
                'url'       => $notif['data'] ? (json_decode($notif['data'], true)['url'] ?? '#') : '#',
            ];
        }

        return $this->response->setJSON([
            'notifications' => $formatted,
            'unread_count'  => $this->notificationService->getUnreadCount($userId)
        ]);
    }

    /**
     * Mark notification as read
     * AJAX endpoint
     */
    public function markAsRead()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $id = $this->request->getPost('id') ?? $this->request->getGet('id');
        
        if (!$id) {
            return $this->response->setJSON(['error' => 'ID required'])->setStatusCode(400);
        }

        $success = $this->notificationService->markAsRead((int)$id, $userId);

        if ($success) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['error' => 'Failed to mark as read'])->setStatusCode(500);
    }

    /**
     * Mark all notifications as read
     * AJAX endpoint
     */
    public function markAllAsRead()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $success = $this->notificationService->markAllAsRead($userId);

        if ($success) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['error' => 'Failed to mark all as read'])->setStatusCode(500);
    }

    /**
     * Show notification settings page
     */
    public function settings()
    {
        $userId = user_id();
        if (!$userId) {
            return redirect()->to('login');
        }

        $preferences = $this->notificationService->getPreferences($userId);
        
        // Default values if not set
        if (!$preferences) {
            $preferences = [
                'enable_inapp' => 1,
                'enable_email' => 1,
                'enable_whatsapp' => 0,
            ];
        }

        $data = [
            'title' => 'Pengaturan Notifikasi',
            'preferences' => $preferences,
        ];

        return view('notifications/settings', $data);
    }

    /**
     * Update notification preferences
     */
    public function updateSettings()
    {
        $userId = user_id();
        if (!$userId) {
            return redirect()->to('login')->with('error', 'Unauthorized');
        }

        $rules = [
            'enable_inapp' => 'permit_empty|in_list[0,1]',
            'enable_email' => 'permit_empty|in_list[0,1]',
            'enable_whatsapp' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'enable_inapp' => $this->request->getPost('enable_inapp') ? 1 : 0,
            'enable_email' => $this->request->getPost('enable_email') ? 1 : 0,
            'enable_whatsapp' => $this->request->getPost('enable_whatsapp') ? 1 : 0,
        ];

        $success = $this->notificationService->updatePreferences($userId, $data);

        if ($success) {
            return redirect()->to('notifications/settings')->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaturan.');
    }
}
