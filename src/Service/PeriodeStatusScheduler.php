<?php

namespace App\Service;

use App\Model\PeriodeModel;
use PDO;

class PeriodeStatusScheduler
{
    private PeriodeModel $model;
    private PDO $db;

    public function __construct(PeriodeModel $model, PDO $db)
    {
        $this->model = $model;
        $this->db = $db;
    }

    /**
     * Jalankan fungsi ini via Cron Job (misal: setiap hari pukul 00:01)
     * php cli/update_status.php
     */
    public function syncStatuses(): array
    {
        $today = date('Y-m-d');
        $changedPeriods = [];

        // 1. Cari yang seharusnya 'aktif' tapi masih 'draft' atau 'selesai'
        // Kondisi: Mulai <= Today <= Selesai
        $sqlActivate = "SELECT id, status FROM periode_survei 
                        WHERE deleted_at IS NULL 
                        AND tanggal_mulai <= :today 
                        AND tanggal_selesai >= :today 
                        AND status != 'aktif'";
        
        $stmt = $this->db->prepare($sqlActivate);
        $stmt->execute([':today' => $today]);
        $toActivate = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($toActivate as $p) {
            $this->model->updateStatus($p['id'], 'aktif');
            $changedPeriods[] = [
                'id' => $p['id'],
                'old_status' => $p['status'],
                'new_status' => 'aktif'
            ];
            // Trigger Event/Queue disini
            $this->triggerStatusChangeEvent($p['id'], $p['status'], 'aktif');
        }

        // 2. Cari yang seharusnya 'selesai' tapi masih 'aktif' atau 'draft'
        // Kondisi: Today > Selesai
        $sqlFinish = "SELECT id, status FROM periode_survei 
                      WHERE deleted_at IS NULL 
                      AND tanggal_selesai < :today 
                      AND status != 'selesai'";
        
        $stmt = $this->db->prepare($sqlFinish);
        $stmt->execute([':today' => $today]);
        $toFinish = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($toFinish as $p) {
            $this->model->updateStatus($p['id'], 'selesai');
            $changedPeriods[] = [
                'id' => $p['id'],
                'old_status' => $p['status'],
                'new_status' => 'selesai'
            ];
            // Trigger Event/Queue disini
            $this->triggerStatusChangeEvent($p['id'], $p['status'], 'selesai');
        }

        return $changedPeriods;
    }

    /**
     * Trigger event untuk kalkulasi (Mockup Queue Job)
     */
    private function triggerStatusChangeEvent(int $periodeId, string $old, string $new): void
    {
        // Dalam aplikasi nyata, dispatch ke message queue (RabbitMQ/Redis)
        // atau simpan ke tabel 'jobs' untuk diproses worker.
        
        error_log("EVENT FIRED: Periode {$periodeId} changed from {$old} to {$new}. Triggering calculation job...");
        
        // Contoh: Insert ke tabel jobs
        // $this->db->prepare("INSERT INTO jobs (...) VALUES (...)")->execute(...);
    }
}
