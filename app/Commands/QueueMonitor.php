<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\QueueService;

/**
 * Queue Monitor Command
 * 
 * Display queue statistics and worker status
 * Usage: php spark queue:monitor [--watch]
 */
class QueueMonitor extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:monitor';
    protected $description = 'Menampilkan statistik queue dan worker';
    protected $usage = 'queue:monitor [--watch]';
    protected $arguments = [];
    protected $options = [
        '--watch' => 'Watch mode (refresh setiap 2 detik)',
        '--json' => 'Output dalam format JSON',
    ];

    protected QueueService $queueService;

    public function run(array $params = [])
    {
        $this->queueService = service('queue');
        
        $watchMode = CLI::getOption('watch') !== null;
        $jsonOutput = CLI::getOption('json') !== null;

        if ($watchMode) {
            $this->runWatchMode();
        } elseif ($jsonOutput) {
            $this->outputJson();
        } else {
            $this->outputStats();
        }
    }

    /**
     * Output stats in watch mode (continuous refresh)
     */
    protected function runWatchMode(): void
    {
        CLI::write("Queue Monitor - Watch Mode (Ctrl+C to exit)", 'green');
        CLI::newLine();

        while (true) {
            // Clear screen
            echo "\033[H\033[J";
            
            $this->outputStats();
            
            sleep(2);
        }
    }

    /**
     * Output stats as JSON
     */
    protected function outputJson(): void
    {
        $stats = $this->queueService->getAllStats();
        $workers = $this->queueService->getWorkerStatus();

        $output = [
            'timestamp' => date('Y-m-d H:i:s'),
            'queues' => $stats,
            'workers' => $workers,
            'summary' => $this->calculateSummary($stats),
        ];

        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        CLI::newLine();
    }

    /**
     * Output formatted stats
     */
    protected function outputStats(): void
    {
        $stats = $this->queueService->getAllStats();
        $workers = $this->queueService->getWorkerStatus();

        CLI::write("═══════════════════════════════════════════════════════════", 'blue');
        CLI::write("                    QUEUE MONITOR                          ", 'blue');
        CLI::write("═══════════════════════════════════════════════════════════", 'blue');
        CLI::write("Timestamp: " . date('Y-m-d H:i:s'), 'cyan');
        CLI::newLine();

        // Queue Statistics
        CLI::write("┌─────────────────────────────────────────────────────────┐", 'white');
        CLI::write("│  QUEUE STATISTICS                                       │", 'white');
        CLI::write("└─────────────────────────────────────────────────────────┘", 'white');
        CLI::newLine();

        $headers = ['Queue Name', 'Pending', 'Processing', 'Completed', 'Failed'];
        $rows = [];

        foreach ($stats as $queueName => $queueStats) {
            if ($queueName === 'failed_jobs') {
                continue;
            }

            $pending = $queueStats['pending'] ?? 0;
            $processing = $queueStats['processing'] ?? 0;
            $completed = $queueStats['completed'] ?? 0;
            $failed = $queueStats['failed'] ?? 0;

            // Color coding for pending
            $pendingColor = 'white';
            if ($pending > 100) {
                $pendingColor = 'red';
            } elseif ($pending > 50) {
                $pendingColor = 'yellow';
            } elseif ($pending > 0) {
                $pendingColor = 'green';
            }

            $rows[] = [
                $queueName,
                ['value' => $pending, 'color' => $pendingColor],
                $processing,
                $completed,
                ['value' => $failed, 'color' => $failed > 0 ? 'red' : 'green'],
            ];
        }

        $this->printTable($headers, $rows);

        // Failed Jobs Count
        if (isset($stats['failed_jobs'])) {
            $failedCount = $stats['failed_jobs']['count'] ?? 0;
            $color = $failedCount > 0 ? 'red' : 'green';
            CLI::newLine();
            CLI::write("Total Failed Jobs in DB: [" . $color . "]{$failedCount}[/]", true);
        }

        CLI::newLine();

        // Worker Status
        CLI::write("┌─────────────────────────────────────────────────────────┐", 'white');
        CLI::write("│  WORKER STATUS                                          │", 'white');
        CLI::write("└─────────────────────────────────────────────────────────┘", 'white');
        CLI::newLine();

        if (empty($workers)) {
            CLI::write("No active workers found.", 'yellow');
        } else {
            $workerHeaders = ['Worker ID', 'Queue', 'PID', 'Status', 'Memory', 'Last Heartbeat'];
            $workerRows = [];

            foreach ($workers as $worker) {
                $status = $worker['status'] ?? 'unknown';
                $statusColor = $status === 'active' ? 'green' : 'yellow';
                
                $memoryMB = round(($worker['memory_usage'] ?? 0) / 1024 / 1024, 2);
                
                $uptime = $this->calculateUptime($worker['started_at'] ?? '');

                $workerRows[] = [
                    substr($worker['worker_id'] ?? '', 0, 30) . '...',
                    $worker['queue'] ?? 'unknown',
                    $worker['pid'] ?? '?',
                    ['value' => $status, 'color' => $statusColor],
                    "{$memoryMB} MB",
                    $uptime,
                ];
            }

            $this->printTable($workerHeaders, $workerRows);
        }

        CLI::newLine();

        // Summary
        $summary = $this->calculateSummary($stats);
        CLI::write("┌─────────────────────────────────────────────────────────┐", 'white');
        CLI::write("│  SUMMARY                                                │", 'white');
        CLI::write("└─────────────────────────────────────────────────────────┘", 'white');
        CLI::newLine();
        
        CLI::write("  Total Pending:     " . $summary['total_pending'], $summary['total_pending'] > 100 ? 'red' : 'green');
        CLI::write("  Total Processing:  " . $summary['total_processing'], 'cyan');
        CLI::write("  Total Completed:   " . $summary['total_completed'], 'green');
        CLI::write("  Total Failed:      " . $summary['total_failed'], $summary['total_failed'] > 0 ? 'red' : 'green');
        CLI::write("  Active Workers:    " . count($workers), 'magenta');
        
        CLI::newLine();
        CLI::write("═══════════════════════════════════════════════════════════", 'blue');
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary(array $stats): array
    {
        $summary = [
            'total_pending' => 0,
            'total_processing' => 0,
            'total_completed' => 0,
            'total_failed' => 0,
        ];

        foreach ($stats as $queueName => $queueStats) {
            if ($queueName === 'failed_jobs') {
                continue;
            }

            $summary['total_pending'] += $queueStats['pending'] ?? 0;
            $summary['total_processing'] += $queueStats['processing'] ?? 0;
            $summary['total_completed'] += $queueStats['completed'] ?? 0;
            $summary['total_failed'] += $queueStats['failed'] ?? 0;
        }

        return $summary;
    }

    /**
     * Calculate uptime from started_at timestamp
     */
    protected function calculateUptime(string $startedAt): string
    {
        if (empty($startedAt)) {
            return 'Unknown';
        }

        $start = strtotime($startedAt);
        $now = time();
        $diff = $now - $start;

        if ($diff < 60) {
            return "{$diff}s";
        } elseif ($diff < 3600) {
            return round($diff / 60) . "m";
        } elseif ($diff < 86400) {
            return round($diff / 3600) . "h";
        } else {
            return round($diff / 86400) . "d";
        }
    }

    /**
     * Print a simple table
     */
    protected function printTable(array $headers, array $rows): void
    {
        if (empty($rows)) {
            CLI::write("No data available.", 'yellow');
            return;
        }

        // Calculate column widths
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = strlen($header);
            foreach ($rows as $row) {
                $value = is_array($row[$i]) ? $row[$i]['value'] : $row[$i];
                $widths[$i] = max($widths[$i], strlen($value));
            }
            $widths[$i] = min($widths[$i], 20); // Max width
        }

        // Print header
        $headerLine = '| ';
        foreach ($headers as $i => $header) {
            $headerLine .= str_pad($header, $widths[$i]) . ' | ';
        }
        CLI::write($headerLine, 'bold');

        // Print separator
        $separator = '+';
        foreach ($widths as $width) {
            $separator .= str_repeat('-', $width + 2) . '+';
        }
        CLI::write($separator);

        // Print rows
        foreach ($rows as $row) {
            $rowLine = '| ';
            foreach ($row as $i => $cell) {
                $value = is_array($cell) ? $cell['value'] : $cell;
                $color = is_array($cell) ? $cell['color'] : null;
                
                $padded = str_pad($value, $widths[$i]);
                
                if ($color) {
                    $rowLine .= "[{$color}]{$padded}[/] | ";
                } else {
                    $rowLine .= "{$padded} | ";
                }
            }
            CLI::write($rowLine, true);
        }
    }
}
