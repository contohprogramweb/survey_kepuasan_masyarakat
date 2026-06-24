<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi Queue System dengan Redis
 */
class Queue extends BaseConfig
{
    /**
     * Default queue connection
     */
    public string $default = 'redis';

    /**
     * Queue connections
     */
    public array $connections = [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 300,
            'block_for' => 5,
            'after_commit' => false,
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 300,
            'after_commit' => false,
        ],
        'sync' => [
            'driver' => 'sync',
        ],
    ];

    /**
     * Queue prefixes
     */
    public string $prefix = 'ikm_queue_';

    /**
     * Failed jobs table
     */
    public string $failedTable = 'failed_jobs';
}
