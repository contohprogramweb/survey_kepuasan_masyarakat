<?php

namespace App\Model;

use PDO;
use DateTime;
use Exception;

class PeriodeModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all periods for DataTables
     */
    public function getDatatable(array $request): array
    {
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        
        $whereClause = "WHERE p.deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (p.nama_periode LIKE :search OR u.nama_unit LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        // Join dengan unit_layanan untuk nama unit
        $countSql = "SELECT COUNT(*) FROM periode_survei p 
                     JOIN unit_layanan u ON p.unit_id = u.id {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $recordsFiltered = $stmt->fetchColumn();

        $totalSql = "SELECT COUNT(*) FROM periode_survei WHERE deleted_at IS NULL";
        $recordsTotal = $this->db->query($totalSql)->fetchColumn();

        $sql = "SELECT p.id, p.nama_periode, p.tanggal_mulai, p.tanggal_selesai, p.status, 
                       u.nama_unit as unit_nama, p.created_at
                FROM periode_survei p
                JOIN unit_layanan u ON p.unit_id = u.id
                {$whereClause}
                ORDER BY p.tanggal_mulai DESC
                LIMIT :start, :length";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) $stmt->bindValue($key, $val);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format tanggal & hitung status real-time untuk display (opsional)
        foreach ($data as &$row) {
            $row['status_display'] = $this->calculateRealTimeStatus($row['status'], $row['tanggal_mulai'], $row['tanggal_selesai']);
        }

        return [
            'draw' => intval($draw),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $data
        ];
    }

    /**
     * Find by ID with Unit info
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.nama_unit as unit_nama 
            FROM periode_survei p
            JOIN unit_layanan u ON p.unit_id = u.id
            WHERE p.id = :id AND p.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Validasi Tumpang Tindih (Overlap)
     * Rumus overlap: (StartA <= EndB) and (EndA >= StartB)
     */
    public function hasOverlap(int $unitId, string $start, string $end, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM periode_survei 
                WHERE unit_id = :unit 
                AND deleted_at IS NULL
                AND (:start <= tanggal_selesai AND :end >= tanggal_mulai)";
        
        if ($excludeId) {
            $sql .= " AND id != :exclude";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':unit', $unitId, PDO::PARAM_INT);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        if ($excludeId) {
            $stmt->bindValue(':exclude', $excludeId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Cek apakah periode memiliki data survei (relasi)
     */
    public function hasSurveyData(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM survei WHERE periode_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create Periode
     */
    public function create(array $data): int
    {
        // Status awal selalu draft saat dibuat manual
        $data['status'] = 'draft'; 
        
        $sql = "INSERT INTO periode_survei (unit_id, nama_periode, tanggal_mulai, tanggal_selesai, status) 
                VALUES (:unit, :nama, :mulai, :selesai, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':unit' => $data['unit_id'],
            ':nama' => $data['nama_periode'],
            ':mulai' => $data['tanggal_mulai'],
            ':selesai' => $data['tanggal_selesai'],
            ':status' => $data['status']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update Periode
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE periode_survei SET 
                unit_id = :unit, 
                nama_periode = :nama, 
                tanggal_mulai = :mulai, 
                tanggal_selesai = :selesai 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':unit' => $data['unit_id'],
            ':nama' => $data['nama_periode'],
            ':mulai' => $data['tanggal_mulai'],
            ':selesai' => $data['tanggal_selesai']
        ]);
    }

    /**
     * Soft Delete
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE periode_survei SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update Status Manual/Scripted
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE periode_survei SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    /**
     * Scope: Active Periods (Database level filter)
     */
    public function getActivePeriods(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT * FROM periode_survei 
            WHERE status = 'aktif' 
            AND tanggal_mulai <= :today 
            AND tanggal_selesai >= :today
            AND deleted_at IS NULL
        ");
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Scope: Upcoming Periods
     */
    public function getUpcomingPeriods(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT * FROM periode_survei 
            WHERE tanggal_mulai > :today 
            AND deleted_at IS NULL
            ORDER BY tanggal_mulai ASC
        ");
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Scope: Ended Periods
     */
    public function getEndedPeriods(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT * FROM periode_survei 
            WHERE tanggal_selesai < :today 
            AND deleted_at IS NULL
            ORDER BY tanggal_selesai DESC
        ");
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Hitung status real-time berdasarkan tanggal
     */
    private function calculateRealTimeStatus(string $currentStatus, string $start, string $end): string
    {
        $today = date('Y-m-d');
        if ($today < $start) return 'upcoming';
        if ($today > $end) return 'selesai';
        return 'aktif';
    }

    /**
     * Get All Units for Dropdown
     */
    public function getUnits(): array
    {
        $stmt = $this->db->query("SELECT id, nama_unit FROM unit_layanan WHERE status = 'aktif' AND deleted_at IS NULL ORDER BY nama_unit");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
