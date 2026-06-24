<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitLayananModel extends Model
{
    protected $table = 'tb_unit_layanan';
    protected $primaryKey = 'id_unit';
    protected $allowedFields = [
        'nama_unit',
        'kode_unit',
        'alamat',
        'kepala_unit',
        'email',
        'telepon',
        'logo_path',
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
}
