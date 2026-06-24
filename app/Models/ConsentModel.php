<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsentModel extends Model
{
    protected $table = 'tb_consent_log';
    protected $primaryKey = 'id_consent';
    protected $allowedFields = [
        'id_responden',
        'consent_type',
        'consent_given',
        'consent_version',
        'consent_text',
        'ip_address',
        'user_agent',
        'device_fingerprint',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;
    protected $deletedField = null;

    /**
     * Consent types as per UU PDP
     */
    public const CONSENT_SURVEY = 'survey_participation';
    public const CONSENT_FOLLOWUP = 'followup_contact';
    public const CONSENT_PUBLICATION = 'anonymous_publication';

    /**
     * Log consent given by respondent
     */
    public function logConsent(int $respondenId, string $consentType, bool $given, array $context = []): int
    {
        $db = \Config\Database::connect();
        
        $data = [
            'id_responden' => $respondenId,
            'consent_type' => $consentType,
            'consent_given' => $given ? 1 : 0,
            'consent_version' => '1.0',
            'consent_text' => $this->getConsentText($consentType),
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'device_fingerprint' => $context['device_fingerprint'] ?? null,
        ];
        
        $builder = $db->table($this->table);
        $builder->insert($data);
        
        return (int)$db->insertID();
    }

    /**
     * Get consent text for each type (Bahasa Indonesia - UU PDP compliant)
     */
    public function getConsentText(string $consentType): string
    {
        $texts = [
            self::CONSENT_SURVEY => 'Saya setuju data saya digunakan untuk kalkulasi Indeks Kepuasan Masyarakat (IKM) dan peningkatan kualitas pelayanan publik.',
            self::CONSENT_FOLLOWUP => 'Saya setuju untuk dihubungi oleh petugas untuk tindak lanjut terkait survei ini (opsional).',
            self::CONSENT_PUBLICATION => 'Saya setuju data saya dipublikasikan secara anonim untuk transparansi hasil survei (opsional).',
        ];
        
        return $texts[$consentType] ?? '';
    }

    /**
     * Get all consents for a respondent
     */
    public function getRespondentConsents(int $respondenId): array
    {
        $query = $this->where('id_responden', $respondenId)->findAll();
        
        $consents = [];
        foreach ($query as $row) {
            $consents[$row['consent_type']] = [
                'given' => $row['consent_given'] == 1,
                'timestamp' => $row['created_at'],
                'version' => $row['consent_version'],
            ];
        }
        
        return $consents;
    }

    /**
     * Check if respondent has given specific consent
     */
    public function hasConsent(int $respondenId, string $consentType): bool
    {
        $result = $this->where('id_responden', $respondenId)
                       ->where('consent_type', $consentType)
                       ->where('consent_given', 1)
                       ->first();
        
        return $result !== null;
    }

    /**
     * Get consent statistics
     */
    public function getStatistics(int $idPeriode): array
    {
        $db = \Config\Database::connect();
        
        $stats = [];
        
        // Survey participation consent
        $surveyQuery = $db->table($this->table)
            ->selectCount('id_consent', 'count')
            ->where('consent_type', self::CONSENT_SURVEY)
            ->where('consent_given', 1)
            ->join('tb_responden r', 'r.id_responden = tb_consent_log.id_responden')
            ->where('r.id_periode', $idPeriode)
            ->get()
            ->getRowArray();
        $stats['survey_participation'] = $surveyQuery['count'] ?? 0;
        
        // Follow-up consent
        $followupQuery = $db->table($this->table)
            ->selectCount('id_consent', 'count')
            ->where('consent_type', self::CONSENT_FOLLOWUP)
            ->where('consent_given', 1)
            ->join('tb_responden r', 'r.id_responden = tb_consent_log.id_responden')
            ->where('r.id_periode', $idPeriode)
            ->get()
            ->getRowArray();
        $stats['followup_contact'] = $followupQuery['count'] ?? 0;
        
        // Publication consent
        $pubQuery = $db->table($this->table)
            ->selectCount('id_consent', 'count')
            ->where('consent_type', self::CONSENT_PUBLICATION)
            ->where('consent_given', 1)
            ->join('tb_responden r', 'r.id_responden = tb_consent_log.id_responden')
            ->where('r.id_periode', $idPeriode)
            ->get()
            ->getRowArray();
        $stats['anonymous_publication'] = $pubQuery['count'] ?? 0;
        
        return $stats;
    }

    /**
     * Export consent logs for audit (UU PDP compliance)
     */
    public function exportForAudit(?int $respondenId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->select('tb_consent_log.*, r.nama, r.email')
                      ->from($this->table)
                      ->join('tb_responden r', 'r.id_responden = tb_consent_log.id_responden', 'left');
        
        if ($respondenId !== null) {
            $query->where('tb_consent_log.id_responden', $respondenId);
        }
        
        if ($startDate !== null) {
            $query->where('tb_consent_log.created_at >=', $startDate);
        }
        
        if ($endDate !== null) {
            $query->where('tb_consent_log.created_at <=', $endDate);
        }
        
        return $query->get()->getResultArray();
    }
}
