<?php

namespace App\Models;

use CodeIgniter\Model;

class QRCodeModel extends Model
{
    protected $table = 'tb_qr_code';
    protected $primaryKey = 'id_qr';
    protected $allowedFields = [
        'id_unit',
        'id_periode',
        'short_url',
        'qr_data',
        'format',
        'file_path',
        'scan_count',
        'last_scanned_at',
        'is_active',
        'expires_at',
        'size_preset',
        'logo_path',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;
    
    protected $validationRules = [
        'id_unit' => 'required|integer',
        'id_periode' => 'permit_empty|integer',
        'short_url' => 'required|alpha_dash|is_unique[tb_qr_code.short_url,id_qr,{id_qr}]',
        'format' => 'required|in_list[svg,png,pdf]',
        'size_preset' => 'permit_empty|in_list[S,M,L,XL]',
    ];
    
    protected $validationMessages = [
        'short_url' => [
            'is_unique' => 'Short URL sudah digunakan.',
        ],
    ];
    
    /**
     * Get QR codes by unit and periode
     */
    public function getByUnitPeriode(int $idUnit, ?int $idPeriode = null): array
    {
        $builder = $this->where('id_unit', $idUnit);
        
        if ($idPeriode !== null) {
            $builder->where('id_periode', $idPeriode);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get QR code by short URL
     */
    public function getByShortUrl(string $shortUrl): ?array
    {
        return $this->where('short_url', $shortUrl)
                    ->where('is_active', 1)
                    ->first();
    }
    
    /**
     * Increment scan count
     */
    public function incrementScanCount(int $idQr): bool
    {
        return $this->update($idQr, [
            'scan_count' => $this->increment('scan_count'),
            'last_scanned_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Get QR codes with unit and periode info
     */
    public function getWithRelations(?int $idUnit = null, ?int $idPeriode = null): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('tb_qr_code.*, tb_unit_layanan.nama_unit, tb_periode.nama_periode');
        $builder->join('tb_unit_layanan', 'tb_unit_layanan.id_unit = tb_qr_code.id_unit');
        $builder->join('tb_periode', 'tb_periode.id_periode = tb_qr_code.id_periode', 'left');
        
        if ($idUnit !== null) {
            $builder->where('tb_qr_code.id_unit', $idUnit);
        }
        
        if ($idPeriode !== null) {
            $builder->where('tb_qr_code.id_periode', $idPeriode);
        }
        
        return $builder->orderBy('tb_qr_code.created_at', 'DESC')->get()->getResultArray();
    }
    
    /**
     * Get active QR codes that haven't expired
     */
    public function getActiveNotExpired(): array
    {
        return $this->where('is_active', 1)
                    ->groupStart()
                        ->where('expires_at', null)
                        ->orWhere('expires_at >', date('Y-m-d H:i:s'))
                    ->groupEnd()
                    ->findAll();
    }
    
    /**
     * Check if short URL exists
     */
    public function shortUrlExists(string $shortUrl, ?int $excludeId = null): bool
    {
        $builder = $this->where('short_url', $shortUrl);
        
        if ($excludeId !== null) {
            $builder->where('id_qr !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
}
