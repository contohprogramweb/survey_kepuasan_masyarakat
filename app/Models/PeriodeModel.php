<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodeModel extends Model
{
    protected $table = 'tb_periode';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_periode',
        'tahun',
        'urutan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_published',
        'keterangan'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /**
     * Get only published periods
     * 
     * @return array
     */
    public function getPublishedPeriodes()
    {
        return $this->where('is_published', 1)
                    ->orderBy('tahun', 'DESC')
                    ->orderBy('urutan', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get active periods (currently running)
     * 
     * @return array
     */
    public function getActivePeriodes()
    {
        $today = date('Y-m-d');
        return $this->where('status', 'active')
                    ->where('tanggal_selesai >=', $today)
                    ->findAll();
    }
}
