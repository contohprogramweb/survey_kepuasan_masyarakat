<?php

namespace App\Model;

use PDO;
use PDOException;
use Exception;

class UnitModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all units for DataTables server-side processing
     */
    public function getDatatable(array $request): array
    {
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        $orderCol = $request['order'][0]['column'] ?? 0;
        $orderDir = $request['order'][0]['dir'] ?? 'ASC';
        
        $columns = ['id', 'kode_unit', 'nama_unit', 'status', 'created_at'];
        $orderColumn = $columns[$orderCol] ?? 'id';

        // Base query (hanya yang tidak di-soft delete)
        $whereClause = "WHERE u.deleted_at IS NULL";
        $params = [];

        // Search filter
        if (!empty($search)) {
            $whereClause .= " AND (u.nama_unit LIKE :search OR u.kode_unit LIKE :search OR u.deskripsi LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        // Status filter (jika ada di request custom)
        if (isset($request['filters']['status']) && $request['filters']['status'] !== '') {
            $whereClause .= " AND u.status = :status";
            $params[':status'] = $request['filters']['status'];
        }

        // Count total filtered
        $countSql = "SELECT COUNT(*) FROM unit_layanan u {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $recordsFiltered = $stmt->fetchColumn();

        // Count total records (tanpa filter search)
        $totalSql = "SELECT COUNT(*) FROM unit_layanan WHERE deleted_at IS NULL";
        $stmt = $this->db->query($totalSql);
        $recordsTotal = $stmt->fetchColumn();

        // Main query
        $sql = "SELECT u.id, u.kode_unit, u.nama_unit, u.deskripsi, u.status, u.created_at 
                FROM unit_layanan u 
                {$whereClause} 
                ORDER BY {$orderColumn} {$orderDir} 
                LIMIT :start, :length";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'draw' => intval($draw),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $data
        ];
    }

    /**
     * Find unit by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM unit_layanan WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Validate unique nama_unit (exclude current ID for updates)
     */
    public function isNamaUnitUnique(string $namaUnit, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM unit_layanan WHERE nama_unit = :nama AND deleted_at IS NULL";
        if ($excludeId) {
            $sql .= " AND id != :exclude";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nama', $namaUnit);
        if ($excludeId) {
            $stmt->bindValue(':exclude', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Check if unit has relations to periode (cannot be deleted)
     */
    public function hasRelations(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM periode WHERE unit_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create new unit
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO unit_layanan (kode_unit, nama_unit, deskripsi, alamat, kontak, status) 
                VALUES (:kode, :nama, :desk, :alamat, :kontak, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':kode' => $data['kode_unit'],
            ':nama' => $data['nama_unit'],
            ':desk' => $data['deskripsi'] ?? null,
            ':alamat' => $data['alamat'] ?? null,
            ':kontak' => $data['kontak'] ?? null,
            ':status' => $data['status'] ?? 'aktif'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update unit
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE unit_layanan SET 
                kode_unit = :kode, 
                nama_unit = :nama, 
                deskripsi = :desk, 
                alamat = :alamat, 
                kontak = :kontak, 
                status = :status 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':kode' => $data['kode_unit'],
            ':nama' => $data['nama_unit'],
            ':desk' => $data['deskripsi'] ?? null,
            ':alamat' => $data['alamat'] ?? null,
            ':kontak' => $data['kontak'] ?? null,
            ':status' => $data['status']
        ]);
    }

    /**
     * Soft delete unit
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE unit_layanan SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Toggle status aktif/nonaktif
     */
    public function toggleStatus(int $id): bool
    {
        $unit = $this->findById($id);
        if (!$unit) return false;

        $newStatus = $unit['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $stmt = $this->db->prepare("UPDATE unit_layanan SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $newStatus,
            ':id' => $id
        ]);
    }
    
    /**
     * Get all active units for dropdowns
     */
    public function getActiveUnits(): array
    {
        $stmt = $this->db->query("SELECT id, nama_unit FROM unit_layanan WHERE status = 'aktif' AND deleted_at IS NULL ORDER BY nama_unit");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
