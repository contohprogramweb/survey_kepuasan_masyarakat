<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi UU PDP (Perlindungan Data Pribadi)
 * Compliance dengan Undang-Undang No. 27 Tahun 2022
 */
class PDP extends BaseConfig
{
    /**
     * Enable PDP compliance features
     */
    public bool $enabled = true;

    /**
     * Consent management
     */
    public bool $consentRequired = true;
    public int $consentExpiry = 31536000; // 1 year in seconds
    public bool $consentGranular = true; // Separate consent for different purposes

    /**
     * Data retention
     */
    public int $dataRetentionDays = 730; // 2 years
    public bool $autoAnonymize = true;
    public bool $autoDelete = false;

    /**
     * Data subject rights
     */
    public bool $allowAccessRequest = true; // Hak akses
    public bool $allowRectificationRequest = true; // Hak perbaikan
    public bool $allowErasureRequest = true; // Hak penghapusan (right to be forgotten)
    public bool $allowPortabilityRequest = true; // Hak portabilitas data
    public bool $allowObjectionRequest = true; // Hak penolakan

    /**
     * Audit logging
     */
    public bool $auditLogging = true;
    public int $auditRetentionDays = 365; // 1 year
    public array $auditEvents = [
        'data_access',
        'data_create',
        'data_update',
        'data_delete',
        'consent_given',
        'consent_withdrawn',
        'data_export',
        'data_anonymized',
    ];

    /**
     * Encryption settings untuk data sensitif
     */
    public bool $encryptSensitiveData = true;
    public array $sensitiveFields = [
        'nik',
        'npwp',
        'no_kk',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_lengkap',
        'no_telepon',
        'email',
    ];

    /**
     * Data Processing Agreement (DPA) settings
     */
    public bool $requireDPA = true;
    public string $dpaVersion = '1.0.0';

    /**
     * Privacy Notice settings
     */
    public bool $showPrivacyNotice = true;
    public string $privacyNoticeUrl = '/privacy-notice';
    public bool $requireExplicitConsent = true;

    /**
     * Breach notification
     */
    public bool $breachNotificationEnabled = true;
    public string $breachNotificationEmail = 'admin@ikm.go.id';
    public int $breachNotificationTimeout = 72; // hours (sesuai UU PDP)

    /**
     * Data Protection contact
     */
    public string $dpoName = 'Admin Perlindungan Data';
    public string $dpoEmail = 'admin@ikm.go.id';
    public string $dpoPhone = '';

    /**
     * Cross-border data transfer
     */
    public bool $allowCrossBorderTransfer = false;
    public array $approvedCountries = [];
}
