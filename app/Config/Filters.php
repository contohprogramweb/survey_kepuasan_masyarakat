<?php

namespace Config\Filters;

use CodeIgniter\Config\BaseConfig;

/**
 * Filters Configuration - Register semua filters yang digunakan
 */
class Filters extends BaseConfig
{
    /**
     * Configures an array of Filter Aliases and matching classes
     */
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\Csrf::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars' => \CodeIgniter\Filters\InvalidChars::class,
        
        // Custom IKM Filters
        'auth'      => \App\Filters\AuthFilter::class,
        'rbac'      => \App\Filters\RBACFilter::class,
        'api-auth'  => \App\Filters\ApiAuthFilter::class,
        'guest'     => \App\Filters\GuestFilter::class,
        'mfa'       => \App\Filters\MFAFilter::class,
        'consent'   => \App\Filters\ConsentFilter::class,
        'throttle'  => \CodeIgniter\Filters\Throttle::class,
    ];

    /**
     * This is a definition of the application-specific filter
     * aliases that are applied to every request.
     *
     * @var array<int, string>
     */
    public array $globals = [
        'before' => [
            'csrf',
            // 'auth', // Global auth akan di-handle per route
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
        ],
    ];

    /**
     * List of filter aliases that works on a particular route.
     *
     * @var array<string, array<string, array<int, string>|string>>
     */
    public array $filters = [
        // Admin routes - require authentication
        'admin/*' => ['before' => ['auth']],
        
        // API routes - require API authentication
        'api/v1/*' => ['before' => ['api-auth']],
        
        // Auth routes - only for guests
        'auth/login' => ['before' => ['guest']],
        'auth/register' => ['before' => ['guest']],
        
        // Survey public routes - no auth needed
        'survei/*' => [],
        
        // Health & Metrics - public
        'health' => [],
        'metrics' => [],
    ];

    /**
     * List of filter aliases that work as "after" filters.
     *
     * @var array<string, array<string, array<int, string>|string>>
     */
    public array $afterFilters = [];
}
