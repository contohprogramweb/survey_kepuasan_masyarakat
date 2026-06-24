<?php

namespace App\Jobs;

class WhatsAppJob
{
    protected $apiUrl;
    protected $accessToken;
    protected $phoneNumberId;

    public function __construct()
    {
        // WhatsApp Business API Configuration
        // These should be in .env file
        $this->apiUrl = env('WHATSAPP_API_URL', 'https://graph.facebook.com/v17.0');
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN', '');
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID', '');
    }

    /**
     * Handle WhatsApp sending job
     * 
     * @param int $notifId Notification ID
     * @param int $userId User ID
     * @param string $title Message title
     * @param string $message Message body
     * @param array $data Additional data
     */
    public function handle(int $notifId, int $userId, string $title, string $message, array $data = []): bool
    {
        // Get user phone from database
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

        if (!$user || empty($user['phone'])) {
            log_message('error', "WhatsAppJob: User {$userId} has no phone number");
            return false;
        }

        // Format phone number (remove +, spaces, dashes)
        $phone = preg_replace('/[^0-9]/', '', $user['phone']);
        
        // Ensure international format (add country code if missing)
        if (substr($phone, 0, 1) !== '6' && strlen($phone) < 12) {
            // Assuming Indonesia (+62) as default, adjust as needed
            $phone = '62' . ltrim($phone, '0');
        }

        // Prepare template message
        // Note: Template must be pre-approved by Meta
        $templateName = env('WHATSAPP_TEMPLATE_NAME', 'survey_notification');
        $languageCode = env('WHATSAPP_LANGUAGE_CODE', 'id');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $title
                            ],
                            [
                                'type' => 'text',
                                'text' => $message
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Send request to WhatsApp Business API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/{$this->phoneNumberId}/messages");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            log_message('info', "WhatsAppJob: Message sent to {$phone} for notification {$notifId}");
            return true;
        }

        log_message('error', "WhatsAppJob: Failed to send to {$phone}. HTTP: {$httpCode}, Error: {$error}, Response: {$response}");
        return false;
    }
}
