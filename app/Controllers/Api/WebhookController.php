<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * WebhookController
 * 
 * Controller untuk menerima webhook dari eksternal sistem
 * Endpoint: /api/webhooks/*
 */
class WebhookController extends BaseController
{
    /**
     * POST /api/webhooks/survey-response
     * Handle incoming survey response webhook
     */
    public function surveyResponse()
    {
        // Verify webhook signature (if configured)
        $signature = $this->request->getHeaderLine('X-Webhook-Signature');
        $secret = config('App')->webhookSecret ?? null;
        
        if ($secret && $signature) {
            $payload = $this->request->getBody();
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid signature'
                ])->setStatusCode(401);
            }
        }
        
        $json = $this->request->getJSON(true);
        
        if (!$json) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid JSON'
            ])->setStatusCode(400);
        }
        
        try {
            // Process webhook data
            log_message('info', 'Webhook survey response received: ' . json_encode($json));
            
            // Trigger async processing via queue
            // $this->queue->push(new ProcessSurveyResponseJob($json));
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Webhook processing error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Processing failed'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * POST /api/webhooks/payment-notification
     * Handle payment notification webhook (for paid surveys)
     */
    public function paymentNotification()
    {
        $json = $this->request->getJSON(true);
        
        if (!$json || empty($json['transaction_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid payload'
            ])->setStatusCode(400);
        }
        
        // Process payment notification
        log_message('info', 'Payment webhook: ' . $json['transaction_id']);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Payment notification received'
        ]);
    }
    
    /**
     * POST /api/webhooks/whatsapp-status
     * Handle WhatsApp message status webhook
     */
    public function whatsappStatus()
    {
        $json = $this->request->getJSON(true);
        
        if (!$json) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid payload'
            ])->setStatusCode(400);
        }
        
        // Update message status in database
        log_message('info', 'WhatsApp status webhook: ' . json_encode($json));
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status updated'
        ]);
    }
    
    /**
     * GET /api/webhooks/test
     * Test webhook endpoint
     */
    public function test()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Webhook endpoint is active',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
