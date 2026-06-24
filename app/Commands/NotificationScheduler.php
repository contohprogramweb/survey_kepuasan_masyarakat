<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\NotificationService;
use CodeIgniter\Database\BaseConnection;

class NotificationScheduler extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'notification:scheduler';
    protected $description = 'Check notification conditions and send alerts hourly';

    protected $notificationService;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
        $this->db = \Config\Database::connect();
    }

    public function run(array $params)
    {
        CLI::write('Starting Notification Scheduler...', 'yellow');
        CLI::write('Checking conditions: ' . date('Y-m-d H:i:s'));

        $conditionsChecked = 0;
        $notificationsSent = 0;

        // Condition 1: Periode akan berakhir (dalam 3 hari)
        CLI::write('Checking: Survey periods ending soon...', 'green');
        $sent1 = $this->checkEndingPeriods();
        $conditionsChecked++;
        $notificationsSent += $sent1;

        // Condition 2: Target responden belum tercapai
        CLI::write('Checking: Survey response targets not met...', 'green');
        $sent2 = $this->checkResponseTargets();
        $conditionsChecked++;
        $notificationsSent += $sent2;

        // Condition 3: IKM turun (Index Kepuasan Masyarakat)
        CLI::write('Checking: IKM decrease detected...', 'green');
        $sent3 = $this->checkIKMDrop();
        $conditionsChecked++;
        $notificationsSent += $sent3;

        CLI::newLine();
        CLI::write("Scheduler completed. Conditions checked: {$conditionsChecked}, Notifications sent: {$notificationsSent}", 'cyan');
    }

    /**
     * Check for survey periods ending within 3 days
     */
    protected function checkEndingPeriods(): int
    {
        $count = 0;
        
        // Query surveys ending in 3 days
        $surveys = $this->db->table('surveys')
            ->where('status', 'active')
            ->where('end_date >=', date('Y-m-d'))
            ->where('end_date <=', date('Y-m-d', strtotime('+3 days')))
            ->get()
            ->getResultArray();

        foreach ($surveys as $survey) {
            $daysLeft = floor((strtotime($survey['end_date']) - time()) / 86400);
            
            // Get responsible users (admin, unit head, etc.)
            $users = $this->getResponsibleUsers($survey['unit_id'] ?? null);

            foreach ($users as $user) {
                $title = 'Periode Survei Segera Berakhir';
                $message = "Survei '{$survey['name']}' akan berakhir dalam {$daysLeft} hari pada {$survey['end_date']}. Segera tindak lanjuti.";
                
                $this->notificationService->send(
                    $user['id'],
                    $title,
                    $message,
                    'warning',
                    ['survey_id' => $survey['id'], 'url' => site_url('surveys/' . $survey['id'])]
                );
                $count++;
            }
        }

        if ($count > 0) {
            CLI::write("  → Sent {$count} notifications for ending periods", 'yellow');
        } else {
            CLI::write("  → No ending period notifications needed", 'gray');
        }

        return $count;
    }

    /**
     * Check for surveys where response target is not met
     */
    protected function checkResponseTargets(): int
    {
        $count = 0;

        // Get active surveys
        $surveys = $this->db->table('surveys')
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        foreach ($surveys as $survey) {
            $target = $survey['target_respondents'] ?? 0;
            
            // Count actual responses
            $actual = $this->db->table('responses')
                ->where('survey_id', $survey['id'])
                ->countAllResults();

            if ($target > 0 && $actual < $target) {
                $percentage = round(($actual / $target) * 100, 1);
                $remaining = $target - $actual;

                // Only notify if below 80% target or less than 3 days remaining
                $daysLeft = floor((strtotime($survey['end_date']) - time()) / 86400);
                
                if ($percentage < 80 || $daysLeft <= 3) {
                    $users = $this->getResponsibleUsers($survey['unit_id'] ?? null);

                    foreach ($users as $user) {
                        $title = 'Target Responden Belum Tercapai';
                        $message = "Survei '{$survey['name']}' baru mencapai {$percentage}% ({$actual}/{$target}). Masih kurang {$remaining} responden.";
                        
                        $this->notificationService->send(
                            $user['id'],
                            $title,
                            $message,
                            'danger',
                            ['survey_id' => $survey['id'], 'url' => site_url('surveys/' . $survey['id'])]
                        );
                        $count++;
                    }
                }
            }
        }

        if ($count > 0) {
            CLI::write("  → Sent {$count} notifications for unmet targets", 'yellow');
        } else {
            CLI::write("  → All targets on track", 'gray');
        }

        return $count;
    }

    /**
     * Check for IKM (Index Kepuasan Masyarakat) drop
     */
    protected function checkIKMDrop(): int
    {
        $count = 0;

        // Get latest IKM results per unit
        $units = $this->db->table('units')->get()->getResultArray();

        foreach ($units as $unit) {
            // Get last 2 IKM scores
            $ikmData = $this->db->table('ikm_results')
                ->where('unit_id', $unit['id'])
                ->orderBy('period', 'DESC')
                ->limit(2)
                ->get()
                ->getResultArray();

            if (count($ikmData) >= 2) {
                $current = (float)$ikmData[0]['score'];
                $previous = (float)$ikmData[1]['score'];

                // Check if drop is significant (> 5%)
                if ($current < $previous) {
                    $drop = $previous - $current;
                    $dropPercent = round(($drop / $previous) * 100, 2);

                    if ($dropPercent >= 5) {
                        $users = $this->getResponsibleUsers($unit['id']);

                        foreach ($users as $user) {
                            $title = 'Penurunan IKM Terdeteksi';
                            $message = "IKM Unit '{$unit['name']}' turun dari {$previous} menjadi {$current} (turun {$dropPercent}%). Perlu evaluasi segera.";
                            
                            $this->notificationService->send(
                                $user['id'],
                                $title,
                                $message,
                                'danger',
                                ['unit_id' => $unit['id'], 'url' => site_url('ikm/unit/' . $unit['id'])]
                            );
                            $count++;
                        }
                    }
                }
            }
        }

        if ($count > 0) {
            CLI::write("  → Sent {$count} notifications for IKM drops", 'yellow');
        } else {
            CLI::write("  → No significant IKM drops", 'gray');
        }

        return $count;
    }

    /**
     * Get responsible users for a unit (admin, unit head, etc.)
     */
    protected function getResponsibleUsers(?int $unitId): array
    {
        $users = [];

        // Get all admin users
        $admins = $this->db->table('users')
            ->where('role', 'admin')
            ->get()
            ->getResultArray();
        $users = array_merge($users, $admins);

        // Get unit head if unit specified
        if ($unitId) {
            $unitHead = $this->db->table('users')
                ->where('unit_id', $unitId)
                ->where('role', 'unit_head')
                ->get()
                ->getResultArray();
            $users = array_merge($users, $unitHead);
        }

        // Remove duplicates by user ID
        $unique = [];
        foreach ($users as $user) {
            $unique[$user['id']] = $user;
        }

        return array_values($unique);
    }
}
