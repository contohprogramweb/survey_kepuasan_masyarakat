<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * InstansiModel
 * 
 * Model untuk mengelola data instansi pemerintah
 * Digunakan untuk landing page dan konfigurasi sistem
 * 
 * Berdasarkan SRS F-12 (Pengaturan Sistem) dan F-13 (Homepage/Landing Page)
 */
class InstansiModel extends Model
{
    protected $table = 'tb_instansi';
    protected $primaryKey = 'id_instansi';
    protected $allowedFields = [
        'nama',
        'nama_resmi',
        'deskripsi',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'telepon',
        'fax',
        'email',
        'website',
        'logo',
        'cover_image',
        'slogan',
        'visi',
        'misi',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'tiktok',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;
    
    protected $validationRules = [
        'nama' => 'required|max_length[255]',
        'email' => 'permit_empty|valid_email',
        'telepon' => 'permit_empty|max_length[50]',
        'website' => 'permit_empty|valid_url',
    ];
    
    protected $validationMessages = [
        'nama' => [
            'required' => 'Nama instansi wajib diisi.',
            'max_length' => 'Nama instansi maksimal 255 karakter.'
        ],
        'email' => [
            'valid_email' => 'Format email tidak valid.'
        ],
        'website' => [
            'valid_url' => 'Format URL website tidak valid.'
        ]
    ];

    /**
     * Get instansi data (single record for now)
     * 
     * @return array|null Instansi data or null if not found
     */
    public function getInstansi(): ?array
    {
        return $this->where('is_active', 1)->first();
    }

    /**
     * Get default instansi data for fallback
     * 
     * @return array Default instansi configuration
     */
    public function getDefaultData(): array
    {
        return [
            'nama' => 'Pemerintah Daerah',
            'nama_resmi' => 'Pemerintah Daerah Provinsi/Kabupaten/Kota',
            'deskripsi' => 'Melayani masyarakat dengan sepenuh hati untuk mewujudkan pelayanan publik yang berkualitas.',
            'alamat' => 'Jl. Raya Pemerintahan No. 1',
            'kelurahan' => '',
            'kecamatan' => '',
            'kota' => '',
            'provinsi' => '',
            'kode_pos' => '',
            'telepon' => '(021) 1234567',
            'fax' => '(021) 1234568',
            'email' => 'info@pemerintah.go.id',
            'website' => 'https://www.pemerintah.go.id',
            'logo' => 'default_logo.png',
            'cover_image' => 'default_cover.jpg',
            'slogan' => 'Melayani dengan Hati',
            'visi' => 'Terwujudnya Pelayanan Publik yang Berkualitas dan Berintegritas',
            'misi' => '1. Meningkatkan kualitas pelayanan publik\n2. Mewujudkan tata kelola pemerintahan yang baik\n3. Meningkatkan partisipasi masyarakat',
            'facebook' => '',
            'twitter' => '',
            'instagram' => '',
            'youtube' => '',
            'tiktok' => '',
            'is_active' => 1,
        ];
    }

    /**
     * Get instansi with social media links formatted
     * 
     * @return array Instansi data with formatted social media
     */
    public function getWithSocialMedia(): ?array
    {
        $instansi = $this->getInstansi();
        
        if (!$instansi) {
            return $this->getDefaultData();
        }
        
        // Format social media URLs
        $socialMedia = [];
        
        if (!empty($instansi['facebook'])) {
            $socialMedia['facebook'] = 'https://facebook.com/' . ltrim($instansi['facebook'], '/');
        }
        if (!empty($instansi['twitter'])) {
            $socialMedia['twitter'] = 'https://twitter.com/' . ltrim($instansi['twitter'], '/');
        }
        if (!empty($instansi['instagram'])) {
            $socialMedia['instagram'] = 'https://instagram.com/' . ltrim($instansi['instagram'], '/');
        }
        if (!empty($instansi['youtube'])) {
            $socialMedia['youtube'] = 'https://youtube.com/' . ltrim($instansi['youtube'], '/');
        }
        if (!empty($instansi['tiktok'])) {
            $socialMedia['tiktok'] = 'https://tiktok.com/@' . ltrim($instansi['tiktok'], '@');
        }
        
        $instansi['social_media'] = $socialMedia;
        
        return $instansi;
    }

    /**
     * Update or create instansi data
     * 
     * @param array $data Instansi data
     * @return bool True if successful
     */
    public function upsert(array $data): bool
    {
        $existing = $this->first();
        
        if ($existing) {
            return $this->update($existing['id_instansi'], $data);
        } else {
            return (bool)$this->insert($data);
        }
    }

    /**
     * Upload and set logo
     * 
     * @param string $logoPath Path to logo file
     * @return bool True if successful
     */
    public function setLogo(string $logoPath): bool
    {
        $instansi = $this->getInstansi();
        
        if ($instansi) {
            return $this->update($instansi['id_instansi'], ['logo' => $logoPath]);
        }
        
        return false;
    }

    /**
     * Upload and set cover image
     * 
     * @param string $coverPath Path to cover image file
     * @return bool True if successful
     */
    public function setCoverImage(string $coverPath): bool
    {
        $instansi = $this->getInstansi();
        
        if ($instansi) {
            return $this->update($instansi['id_instansi'], ['cover_image' => $coverPath]);
        }
        
        return false;
    }
}
