<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi Queue System dengan Database
 */
class Queue extends BaseConfig
{
    /**
     * Default queue connection
     */
    public string $default = 'database';

    /**
     * Queue connections
     */
    public array $connections = [
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
