<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitLayananModel extends Model
{
    protected $table = 'tb_unit_layanan';
    protected $primaryKey = 'id_unit';
    protected $allowedFields = [
        'id_instansi',
        'nama_unit',
        'kode_unit',
        'alamat',
        'kepala_unit',
        'email',
        'telepon',
        'logo_path',
        'jenis_unit',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;
    
    /**
     * Get all active units
     */
    public function getActiveUnits(): array
    {
        return $this->where('is_active', 1)->findAll();
    }
    
    /**
     * Get unit by ID with additional info
     */
    public function getUnitWithInfo(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Get all units with instansi info
     */
    public function getAllWithInstansi(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table($this->table . ' u')
            ->select('u.*, i.nama_instansi')
            ->join('tb_instansi i', 'i.id_instansi = u.id_instansi', 'left')
            ->orderBy('u.nama_unit', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Get all instansi
     */
    public function getAllInstansi(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_instansi')
            ->where('is_active', 1)
            ->orderBy('nama_instansi', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Check if unit has users
     */
    public function hasUsers(int $unitId): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_pengguna')
            ->where('id_unit', $unitId);
        
        $result = $query->countAllResults();
        return $result > 0;
    }

    /**
     * Check if unit has survey responses
     */
    public function hasResponses(int $unitId): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_survei_jawaban')
            ->where('id_unit', $unitId);
        
        $result = $query->countAllResults();
        return $result > 0;
    }
}
