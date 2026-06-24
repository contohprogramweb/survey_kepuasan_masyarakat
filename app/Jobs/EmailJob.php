<?php

namespace App\Jobs;

use CodeIgniter\Email\Email;

class EmailJob
{
    /**
     * Handle email sending job
     * 
     * @param int $notifId Notification ID
     * @param int $userId User ID
     * @param string $title Email subject
     * @param string $message Email body
     * @param array $data Additional data
     */
    public function handle(int $notifId, int $userId, string $title, string $message, array $data = []): bool
    {
        // Get user email from database (assuming users table exists)
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

        if (!$user || empty($user['email'])) {
            log_message('error', "EmailJob: User {$userId} has no email address");
            return false;
        }

        $email = new Email();
        
        // Configure email settings (should be in .env or Config/Email.php)
        $config = [
            'protocol'  => config('Email')->protocol ?? 'smtp',
            'SMTPHost'  => config('Email')->SMTPHost ?? '',
            'SMTPPort'  => config('Email')->SMTPPort ?? 465,
            'SMTPUser'  => config('Email')->SMTPUser ?? '',
            'SMTPPass'  => config('Email')->SMTPPass ?? '',
            'mailType'  => 'html',
            'charset'   => 'utf-8',
            'validate'  => true,
            'CRLF'      => "\r\n",
            'wordWrap'  => true,
        ];

        $email->initialize($config);
        $email->setTo($user['email']);
        $email->setFrom(config('Email')->fromEmail ?? 'noreply@example.com', config('Email')->fromName ?? 'Survey System');
        $email->setSubject($title);

        // Prepare email content with template
        $emailContent = $this->renderTemplate($title, $message, $data, $user);
        $email->setMessage($emailContent);

        // Also set plain text alternative
        $email->setAltMessage(strip_tags($emailContent));

        if ($email->send()) {
            log_message('info', "EmailJob: Email sent to {$user['email']} for notification {$notifId}");
            return true;
        }

        log_message('error', "EmailJob: Failed to send email to {$user['email']}. Error: " . $email->printDebugger(['headers']));
        return false;
    }

    /**
     * Render email template
     */
    protected function renderTemplate(string $title, string $message, array $data, array $user): string
    {
        // Load view template
        $viewData = [
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
            'user'    => $user,
            'app_name' => config('App')->siteName ?? 'Survey Management System',
        ];

        return view('email_templates/notification', $viewData);
    }
}
