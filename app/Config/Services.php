<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Services extends BaseConfig
{
    /**
     * Service Container untuk Dependency Injection
     */
    public static function container(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('container');
        }

        return new \App\Services\Container();
    }

    /**
     * Queue Service untuk Redis Queue
     */
    public static function queue(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('queue');
        }

        $config = config('Queue');
        return new \App\Services\QueueService($config);
    }

    /**
     * OAuth2 Service untuk autentikasi eksternal
     */
    public static function oauth2(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('oauth2');
        }

        $config = config('OAuth2');
        return new \App\Services\OAuth2Service($config);
    }

    /**
     * MFA Service untuk Multi-Factor Authentication
     */
    public static function mfa(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('mfa');
        }

        return new \App\Services\MFAService();
    }

    /**
     * Consent Service untuk UU PDP Compliance
     */
    public static function consent(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('consent');
        }

        return new \App\Services\ConsentService();
    }

    /**
     * RBAC Service untuk Role-Based Access Control
     */
    public static function rbac(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('rbac');
        }

        return new \App\Services\RBACService();
    }

    /**
     * Audit Service untuk logging aktivitas
     */
    public static function audit(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('audit');
        }

        return new \App\Services\AuditService();
    }
}
