<?php

namespace App\Model;

use PDO;
use Exception;

class KuesionerModel
{
    private PDO $db;

    // 9 Unsur Wajib IKM (Hardcoded sesuai Peraturan Menteri PANRB)
    private const UNSUR_WAJIB = [
        ['code' => 'U1', 'nama' => 'Persyaratan', 'deskripsi' => 'Persyaratan teknis dan administratif'],
        ['code' => 'U2', 'nama' => 'Sistem, Mekanisme, dan Prosedur', 'deskripsi' => 'Sistem, mekanisme, dan prosedur pelayanan'],
        ['code' => 'U3', 'nama' => 'Waktu Pelayanan', 'deskripsi' => 'Waktu penyelesaian pelayanan'],
        ['code' => 'U4', 'nama' => 'Biaya/Tarif', 'deskripsi' => 'Biaya/tarif pelayanan'],
        ['code' => 'U5', 'nama' => 'Produk Spesifikasi Jenis Pelayanan', 'deskripsi' => 'Produk spesifikasi jenis pelayanan'],
        ['code' => 'U6', 'nama' => 'Kompetensi Pelaksana', 'deskripsi' => 'Kompetensi pelaksana pelayanan'],
        ['code' => 'U7', 'nama' => 'Perilaku Pelaksana', 'deskripsi' => 'Perilaku pelaksana pelayanan'],
        ['code' => 'U8', 'nama' => 'Penanganan Pengaduan, Saran, dan Masukan', 'deskripsi' => 'Penanganan pengaduan, saran, dan masukan'],
        ['code' => 'U9', 'nama' => 'Sarana dan Prasarana', 'deskripsi' => 'Sarana dan prasarana pelayanan'],
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all kuesioner for DataTables server-side processing
     */
    public function getDatatable(array $request): array
    {
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $search = $request['search']['value'] ?? '';
        
        $whereClause = "WHERE k.deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (k.nama_unsur LIKE :search OR k.unsur_code LIKE :search OR k.deskripsi LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $countSql = "SELECT COUNT(*) FROM tb_kuesioner k {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $recordsFiltered = $stmt->fetchColumn();

        $totalSql = "SELECT COUNT(*) FROM tb_kuesioner WHERE deleted_at IS NULL";
        $recordsTotal = $this->db->query($totalSql)->fetchColumn();

        $sql = "SELECT k.id_kuesioner, k.unsur_code, k.nama_unsur, k.deskripsi, 
                       k.bobot, k.is_active, k.urutan, k.created_at, k.updated_at
                FROM tb_kuesioner k
                {$whereClause}
                ORDER BY k.urutan ASC, k.unsur_code ASC
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
     * Find kuesioner by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_kuesioner WHERE id_kuesioner = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find kuesioner by unsur code
     */
    public function findByUnsurCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_kuesioner WHERE unsur_code = :code AND deleted_at IS NULL");
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all active kuesioner ordered by urutan
     */
    public function getAllActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM tb_kuesioner WHERE is_active = 1 AND deleted_at IS NULL ORDER BY urutan ASC, unsur_code ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all kuesioner (for preview)
     */
    public function getAllForPreview(): array
    {
        $stmt = $this->db->query("SELECT * FROM tb_kuesioner WHERE is_active = 1 AND deleted_at IS NULL ORDER BY urutan ASC, unsur_code ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Initialize 9 unsur wajib jika belum ada
     */
    public function initializeUnsurWajib(): void
    {
        foreach (self::UNSUR_WAJIB as $index => $unsur) {
            $existing = $this->findByUnsurCode($unsur['code']);
            
            if (!$existing) {
                $this->create([
                    'unsur_code' => $unsur['code'],
                    'nama_unsur' => $unsur['nama'],
                    'deskripsi' => $unsur['deskripsi'],
                    'bobot' => 1.00,
                    'urutan' => $index + 1,
                    'is_active' => 1
                ]);
            }
        }
    }

    /**
     * Create new kuesioner
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO tb_kuesioner (unsur_code, nama_unsur, deskripsi, bobot, is_active, urutan) 
                VALUES (:code, :nama, :desk, :bobot, :active, :urutan)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':code' => $data['unsur_code'],
            ':nama' => $data['nama_unsur'],
            ':desk' => $data['deskripsi'] ?? null,
            ':bobot' => $data['bobot'] ?? 1.00,
            ':active' => $data['is_active'] ?? 1,
            ':urutan' => $data['urutan'] ?? 0
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update kuesioner (hanya teks pertanyaan dan status - unsur_code tidak bisa diubah)
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE tb_kuesioner SET 
                nama_unsur = :nama, 
                deskripsi = :desk, 
                bobot = :bobot,
                is_active = :active,
                urutan = :urutan
                WHERE id_kuesioner = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nama' => $data['nama_unsur'],
            ':desk' => $data['deskripsi'] ?? null,
            ':bobot' => $data['bobot'] ?? 1.00,
            ':active' => $data['is_active'] ?? 1,
            ':urutan' => $data['urutan'] ?? 0
        ]);
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(int $id): bool
    {
        $kuesioner = $this->findById($id);
        if (!$kuesioner) return false;

        $newStatus = $kuesioner['is_active'] == 1 ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE tb_kuesioner SET is_active = :status WHERE id_kuesioner = :id");
        return $stmt->execute([
            ':status' => $newStatus,
            ':id' => $id
        ]);
    }

    /**
     * Soft delete kuesioner (hanya untuk unsur non-wajib)
     * Unsur wajib (U1-U9) tidak bisa dihapus
     */
    public function delete(int $id): bool
    {
        $kuesioner = $this->findById($id);
        if (!$kuesioner) return false;

        // Cek apakah ini unsur wajib (U1-U9)
        if (in_array($kuesioner['unsur_code'], array_column(self::UNSUR_WAJIB, 'code'))) {
            throw new Exception("Unsur wajib IKM (U1-U9) tidak dapat dihapus.");
        }

        $stmt = $this->db->prepare("UPDATE tb_kuesioner SET deleted_at = NOW() WHERE id_kuesioner = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Check if kuesioner is wajib (cannot be deleted)
     */
    public function isUnsurWajib(string $code): bool
    {
        return in_array($code, array_column(self::UNSUR_WAJIB, 'code'));
    }

    /**
     * Get next urutan number
     */
    public function getNextUrutan(): int
    {
        $stmt = $this->db->query("SELECT MAX(urutan) as max_urutan FROM tb_kuesioner WHERE deleted_at IS NULL");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_urutan'] ?? 0) + 1;
    }

    /**
     * Update urutan for multiple kuesioner
     */
    public function updateUrutan(array $items): bool
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($items as $item) {
                $stmt = $this->db->prepare("UPDATE tb_kuesioner SET urutan = :urutan WHERE id_kuesioner = :id");
                $stmt->execute([
                    ':urutan' => $item['urutan'],
                    ':id' => $item['id_kuesioner']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
