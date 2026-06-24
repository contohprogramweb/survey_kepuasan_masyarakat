<?php

namespace App\Models;

use CodeIgniter\Model;

class RespondenModel extends Model
{
    protected $table = 'tb_responden';
    protected $primaryKey = 'id_responden';
    protected $allowedFields = [
        'id_unit',
        'id_periode',
        'nik',
        'nama',
        'jenis_kelamin',
        'usia',
        'pendidikan',
        'pekerjaan',
        'email',
        'telepon',
        'consent_given',
        'consent_timestamp',
        'consent_version',
        'data_retention_date',
        'ip_address',
        'user_agent',
        'device_fingerprint',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;
    protected $validationRules = [
        'id_unit' => 'required|integer',
        'id_periode' => 'required|integer',
        'jenis_kelamin' => 'permit_empty|in_list[L,P]',
        'usia' => 'permit_empty|integer',
        'email' => 'permit_empty|valid_email',
        'telepon' => 'permit_empty|max_length[50]',
    ];

    /**
     * Create respondent with fingerprint for anti-duplication
     */
    public function createRespondent(array $data): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        // Generate device fingerprint
        $data['device_fingerprint'] = $this->generateFingerprint(
            $data['ip_address'] ?? $this->request->getIPAddress(),
            $data['user_agent'] ?? $this->request->getUserAgent()
        );
        
        // Set consent timestamp if consent given
        if (!empty($data['consent_given']) && $data['consent_given'] == 1) {
            $data['consent_timestamp'] = date('Y-m-d H:i:s');
            $data['consent_version'] = '1.0';
            
            // Set data retention date (e.g., 2 years from now per UU PDP)
            $data['data_retention_date'] = date('Y-m-d', strtotime('+2 years'));
        }
        
        $builder->insert($data);
        return (int)$db->insertID();
    }

    /**
     * Generate device fingerprint for anti-duplication
     */
    public function generateFingerprint(string $ipAddress, string $userAgent): string
    {
        // Combine IP, User Agent, and timestamp (hour-based to allow multiple submissions per day)
        $timestamp = date('Y-m-d H');
        return hash('sha256', "{$ipAddress}|{$userAgent}|{$timestamp}");
    }

    /**
     * Check if respondent already submitted in this period
     */
    public function hasSubmitted(int $idUnit, int $idPeriode, string $fingerprint): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table($this->table)
            ->where('id_unit', $idUnit)
            ->where('id_periode', $idPeriode)
            ->where('device_fingerprint', $fingerprint);
        
        $result = $query->countAllResults();
        return $result > 0;
    }

    /**
     * Get respondent by ID
     */
    public function getRespondent(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Anonymize respondent data (for GDPR/UU PDP compliance)
     */
    public function anonymizeData(int $id): bool
    {
        $data = [
            'nik' => null,
            'nama' => null,
            'email' => null,
            'telepon' => null,
        ];
        
        return $this->update($id, $data);
    }
}
