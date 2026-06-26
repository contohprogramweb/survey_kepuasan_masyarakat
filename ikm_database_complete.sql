-- ============================================================================
-- APLIKASI IKM (Indeks Kepuasan Masyarakat) v2.0.0
-- File: ikm_database_complete.sql
-- Deskripsi: Struktur database lengkap dengan data contoh siap pakai
-- Database: MySQL 8.0+ / MariaDB 10.5+
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- BUAT DATABASE (Opsional - uncomment jika ingin membuat database baru)
-- ============================================================================
-- CREATE DATABASE IF NOT EXISTS `db_ikm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `db_ikm`;

-- ============================================================================
-- STRUKTUR TABEL
-- Urutan: Master Data -> Referensi -> Transaksi -> Log/Audit
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. MASTER DATA: tb_instansi (Instansi/Organisasi Induk)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_instansi`;
CREATE TABLE `tb_instansi` (
  `id_instansi` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_instansi` VARCHAR(255) NOT NULL,
  `kode_instansi` VARCHAR(50) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `kota` VARCHAR(100) DEFAULT NULL,
  `provinsi` VARCHAR(100) DEFAULT NULL,
  `kode_pos` VARCHAR(10) DEFAULT NULL,
  `telepon` VARCHAR(50) DEFAULT NULL,
  `fax` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `kepala_instansi` VARCHAR(255) DEFAULT NULL,
  `nip_kepala` VARCHAR(50) DEFAULT NULL,
  `visi` TEXT DEFAULT NULL,
  `misi` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_instansi`),
  UNIQUE KEY `kode_instansi` (`kode_instansi`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. MASTER DATA: tb_unit_layanan (Unit Pelayanan)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_unit_layanan`;
CREATE TABLE `tb_unit_layanan` (
  `id_unit` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_instansi` INT(11) UNSIGNED DEFAULT NULL,
  `nama_unit` VARCHAR(255) NOT NULL,
  `kode_unit` VARCHAR(50) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `kepala_unit` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telepon` VARCHAR(50) DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_unit`),
  UNIQUE KEY `kode_unit` (`kode_unit`),
  KEY `is_active` (`is_active`),
  KEY `fk_unit_instansi` (`id_instansi`),
  CONSTRAINT `fk_unit_instansi` FOREIGN KEY (`id_instansi`) REFERENCES `tb_instansi` (`id_instansi`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. MASTER DATA: tb_periode (Periode Survei)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_periode`;
CREATE TABLE `tb_periode` (
  `id_periode` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_periode` VARCHAR(100) NOT NULL,
  `tahun` INT(4) NOT NULL,
  `bulan_mulai` INT(2) NOT NULL,
  `bulan_selesai` INT(2) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_periode`),
  KEY `periode_range` (`tahun`, `bulan_mulai`, `bulan_selesai`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. MASTER DATA: tb_pengguna (Users/Sistem Pengguna)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_pengguna`;
CREATE TABLE `tb_pengguna` (
  `id_pengguna` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED DEFAULT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin_unit', 'operator', 'viewer') NOT NULL DEFAULT 'operator',
  `mfa_secret` VARCHAR(255) DEFAULT NULL,
  `mfa_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `oauth_provider` VARCHAR(50) DEFAULT NULL,
  `oauth_id` VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_active` (`is_active`),
  KEY `fk_pengguna_unit` (`id_unit`),
  CONSTRAINT `fk_pengguna_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. MASTER DATA: tb_kuesioner (9 Unsur Wajib IKM)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_kuesioner`;
CREATE TABLE `tb_kuesioner` (
  `id_kuesioner` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `unsur_code` VARCHAR(10) NOT NULL COMMENT 'U1-U9',
  `nama_unsur` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `bobot` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan` INT(2) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_kuesioner`),
  UNIQUE KEY `unit_unsur_unique` (`id_unit`, `unsur_code`),
  KEY `is_active` (`is_active`),
  KEY `fk_kuesioner_unit` (`id_unit`),
  CONSTRAINT `fk_kuesioner_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. MASTER DATA: tb_bahasa (Multi-language Support)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_bahasa`;
CREATE TABLE `tb_bahasa` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `locale` VARCHAR(10) NOT NULL,
  `module` VARCHAR(50) NOT NULL DEFAULT 'app',
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale_module_key` (`locale`, `module`, `key`),
  KEY `locale` (`locale`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. MASTER DATA: translations (Translation Keys)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `locale` VARCHAR(10) NOT NULL,
  `translation` TEXT NOT NULL,
  `context` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_locale_unique` (`key`, `locale`),
  KEY `locale` (`locale`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. TRANSAKSI: tb_responden (Data Responden - UU PDP Compliance)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_responden`;
CREATE TABLE `tb_responden` (
  `id_responden` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `id_periode` INT(11) UNSIGNED NOT NULL,
  `nik` VARCHAR(16) DEFAULT NULL COMMENT 'Encrypted',
  `nama` VARCHAR(255) DEFAULT NULL COMMENT 'Encrypted',
  `jenis_kelamin` ENUM('L', 'P') DEFAULT NULL,
  `usia` INT(3) DEFAULT NULL,
  `pendidikan` VARCHAR(100) DEFAULT NULL,
  `pekerjaan` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL COMMENT 'Encrypted',
  `telepon` VARCHAR(50) DEFAULT NULL COMMENT 'Encrypted',
  `consent_given` TINYINT(1) NOT NULL DEFAULT 0,
  `consent_timestamp` DATETIME DEFAULT NULL,
  `consent_version` VARCHAR(20) DEFAULT NULL,
  `data_retention_date` DATE DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_responden`),
  KEY `responden_unit_periode` (`id_unit`, `id_periode`),
  KEY `consent_given` (`consent_given`),
  KEY `created_at` (`created_at`),
  KEY `fk_responden_unit` (`id_unit`),
  KEY `fk_responden_periode` (`id_periode`),
  CONSTRAINT `fk_responden_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_responden_periode` FOREIGN KEY (`id_periode`) REFERENCES `tb_periode` (`id_periode`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. TRANSAKSI: tb_survei_jawaban (Jawaban Survei - PARTITIONED)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_survei_jawaban`;
CREATE TABLE `tb_survei_jawaban` (
  `id_jawaban` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_responden` INT(11) UNSIGNED NOT NULL,
  `id_kuesioner` INT(11) UNSIGNED NOT NULL,
  `id_periode` INT(11) UNSIGNED NOT NULL,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `nilai` INT(1) NOT NULL COMMENT '1-4 (Skala Likert)',
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_jawaban`, `id_periode`),
  KEY `idx_responden` (`id_responden`),
  KEY `idx_kuesioner` (`id_kuesioner`),
  KEY `idx_periode_unit` (`id_periode`, `id_unit`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_jawaban_responden` FOREIGN KEY (`id_responden`) REFERENCES `tb_responden` (`id_responden`) ON DELETE RESTRICT,
  CONSTRAINT `fk_jawaban_kuesioner` FOREIGN KEY (`id_kuesioner`) REFERENCES `tb_kuesioner` (`id_kuesioner`) ON DELETE RESTRICT,
  CONSTRAINT `fk_jawaban_periode` FOREIGN KEY (`id_periode`) REFERENCES `tb_periode` (`id_periode`) ON DELETE RESTRICT,
  CONSTRAINT `fk_jawaban_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (`id_periode`) (
  PARTITION p0 VALUES LESS THAN (10),
  PARTITION p1 VALUES LESS THAN (20),
  PARTITION p2 VALUES LESS THAN (30),
  PARTITION p3 VALUES LESS THAN (40),
  PARTITION p4 VALUES LESS THAN (50),
  PARTITION p5 VALUES LESS THAN (60),
  PARTITION p6 VALUES LESS THAN (70),
  PARTITION p7 VALUES LESS THAN (80),
  PARTITION p8 VALUES LESS THAN (90),
  PARTITION p9 VALUES LESS THAN (100),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);

-- ----------------------------------------------------------------------------
-- 10. TRANSAKSI: tb_saran (Saran dan Masukan)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_saran`;
CREATE TABLE `tb_saran` (
  `id_saran` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_responden` INT(11) UNSIGNED NOT NULL,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `pesan` TEXT NOT NULL,
  `status` ENUM('baru', 'diproses', 'ditindaklanjuti', 'ditolak') NOT NULL DEFAULT 'baru',
  `tanggapan` TEXT DEFAULT NULL,
  `ditanggapi_oleh` INT(11) UNSIGNED DEFAULT NULL,
  `ditanggapi_pada` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_saran`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `fk_saran_responden` (`id_responden`),
  KEY `fk_saran_unit` (`id_unit`),
  KEY `fk_saran_pengguna` (`ditanggapi_oleh`),
  CONSTRAINT `fk_saran_responden` FOREIGN KEY (`id_responden`) REFERENCES `tb_responden` (`id_responden`) ON DELETE RESTRICT,
  CONSTRAINT `fk_saran_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT,
  CONSTRAINT `fk_saran_pengguna` FOREIGN KEY (`ditanggapi_oleh`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 11. TRANSAKSI: tb_rekap_ikm (Rekapitulasi Nilai IKM)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_rekap_ikm`;
CREATE TABLE `tb_rekap_ikm` (
  `id_rekap` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `id_periode` INT(11) UNSIGNED NOT NULL,
  `jumlah_responden` INT(11) NOT NULL DEFAULT 0,
  `nilai_ikm_mentah` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `nilai_ikm_final` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `mutu_layanan` VARCHAR(50) DEFAULT NULL COMMENT 'A/B/C/D',
  `delta_ikm` DECIMAL(5,2) DEFAULT NULL COMMENT 'Perubahan dari periode sebelumnya',
  `flag_alert` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika ada penurunan signifikan',
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `published_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_rekap`),
  UNIQUE KEY `unit_periode_unique` (`id_unit`, `id_periode`),
  KEY `is_published` (`is_published`),
  KEY `flag_alert` (`flag_alert`),
  KEY `fk_rekap_unit` (`id_unit`),
  KEY `fk_rekap_periode` (`id_periode`),
  KEY `fk_rekap_pengguna` (`published_by`),
  CONSTRAINT `fk_rekap_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rekap_periode` FOREIGN KEY (`id_periode`) REFERENCES `tb_periode` (`id_periode`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rekap_pengguna` FOREIGN KEY (`published_by`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. LOG/AUDIT: tb_audit_log (Audit Trail - PARTITIONED BY YEAR)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_audit_log`;
CREATE TABLE `tb_audit_log` (
  `id_log` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pengguna` INT(11) UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100) DEFAULT NULL,
  `record_id` VARCHAR(50) DEFAULT NULL,
  `old_value` TEXT DEFAULT NULL COMMENT 'JSON',
  `new_value` TEXT DEFAULT NULL COMMENT 'JSON',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_log`, `created_at`),
  KEY `idx_action_created` (`action`, `created_at`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (YEAR(`created_at`)) (
  PARTITION p2022 VALUES LESS THAN ('2023-01-01'),
  PARTITION p2023 VALUES LESS THAN ('2024-01-01'),
  PARTITION p2024 VALUES LESS THAN ('2025-01-01'),
  PARTITION p2025 VALUES LESS THAN ('2026-01-01'),
  PARTITION p2026 VALUES LESS THAN ('2027-01-01'),
  PARTITION p2027 VALUES LESS THAN ('2028-01-01'),
  PARTITION p2028 VALUES LESS THAN ('2029-01-01'),
  PARTITION p2029 VALUES LESS THAN ('2030-01-01'),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);

-- ----------------------------------------------------------------------------
-- 13. LOG/AUDIT: tb_notifikasi (Notifikasi Sistem)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_notifikasi`;
CREATE TABLE `tb_notifikasi` (
  `id_notifikasi` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pengguna` INT(11) UNSIGNED DEFAULT NULL,
  `channel` ENUM('email', 'sms', 'whatsapp', 'push', 'in_app') NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_notifikasi`),
  KEY `fk_notifikasi_pengguna` (`id_pengguna`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_notifikasi_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 14. LOG/AUDIT: notifications (Notifikasi User - Alternative)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'warning', 'danger', 'success') NOT NULL DEFAULT 'info',
  `channel` ENUM('inapp', 'email', 'whatsapp') NOT NULL DEFAULT 'inapp',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `data` JSON DEFAULT NULL COMMENT 'Additional data (e.g., survey_id, url)',
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 15. LOG/AUDIT: notification_preferences (Preferensi Notifikasi)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `enable_inapp` TINYINT(1) NOT NULL DEFAULT 1,
  `enable_email` TINYINT(1) NOT NULL DEFAULT 1,
  `enable_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fknotifpref_user` FOREIGN KEY (`user_id`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 16. LOG/AUDIT: tb_backup_log (Log Backup Database)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_backup_log`;
CREATE TABLE `tb_backup_log` (
  `id_backup` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `backup_type` ENUM('full', 'incremental', 'manual') NOT NULL DEFAULT 'full',
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Size in bytes',
  `checksum_md5` VARCHAR(32) DEFAULT NULL,
  `checksum_sha256` VARCHAR(64) DEFAULT NULL,
  `retention_days` INT(11) NOT NULL DEFAULT 30,
  `expires_at` DATE DEFAULT NULL,
  `status` ENUM('success', 'failed', 'deleted') NOT NULL DEFAULT 'success',
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_backup`),
  KEY `status` (`status`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 17. LOG/AUDIT: tb_qr_code (QR Code untuk Survei)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_qr_code`;
CREATE TABLE `tb_qr_code` (
  `id_qr` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED NOT NULL,
  `id_periode` INT(11) UNSIGNED DEFAULT NULL,
  `qr_token` VARCHAR(255) NOT NULL,
  `qr_data` TEXT NOT NULL COMMENT 'JSON encoded data',
  `scan_count` INT(11) NOT NULL DEFAULT 0,
  `last_scanned_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_qr`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `fk_qr_unit` (`id_unit`),
  KEY `fk_qr_periode` (`id_periode`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `fk_qr_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE CASCADE,
  CONSTRAINT `fk_qr_periode` FOREIGN KEY (`id_periode`) REFERENCES `tb_periode` (`id_periode`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 18. LOG/AUDIT: tb_consent_log (Log Consent/Persetujuan - UU PDP)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_consent_log`;
CREATE TABLE `tb_consent_log` (
  `id_consent` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_responden` INT(11) UNSIGNED DEFAULT NULL,
  `consent_type` VARCHAR(100) NOT NULL COMMENT 'survey_participation, data_processing, marketing, etc',
  `consent_given` TINYINT(1) NOT NULL,
  `consent_version` VARCHAR(20) NOT NULL,
  `consent_text` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `device_fingerprint` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_consent`),
  KEY `consent_responden_type` (`id_responden`, `consent_type`),
  KEY `consent_type` (`consent_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_consent_responden` FOREIGN KEY (`id_responden`) REFERENCES `tb_responden` (`id_responden`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 19. QUEUE: tb_queue_jobs (Job Queue Persistence)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_queue_jobs`;
CREATE TABLE `tb_queue_jobs` (
  `id_job` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue_name` VARCHAR(100) NOT NULL,
  `job_class` VARCHAR(255) NOT NULL,
  `payload` TEXT NOT NULL COMMENT 'JSON',
  `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
  `attempts` INT(3) NOT NULL DEFAULT 0,
  `max_attempts` INT(3) NOT NULL DEFAULT 3,
  `reserved_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `failed_at` DATETIME DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `available_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_job`),
  KEY `queue_status` (`queue_name`, `status`),
  KEY `status` (`status`),
  KEY `available_at` (`available_at`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 20. QUEUE: tb_failed_jobs (Failed Jobs Tracking)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_failed_jobs`;
CREATE TABLE `tb_failed_jobs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` VARCHAR(100) NOT NULL COMMENT 'ID unik dari job',
  `queue_name` VARCHAR(50) NOT NULL,
  `payload` TEXT NOT NULL COMMENT 'JSON payload job',
  `exception` TEXT DEFAULT NULL COMMENT 'Stack trace exception',
  `failed_at` DATETIME NOT NULL,
  `retry_count` INT(11) NOT NULL DEFAULT 0,
  `max_retries` INT(11) NOT NULL DEFAULT 3,
  `next_retry_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `queue_name` (`queue_name`),
  KEY `failed_at` (`failed_at`),
  KEY `next_retry_at` (`next_retry_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 21. QUEUE: tb_queue_settings (Queue Configuration)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_queue_settings`;
CREATE TABLE `tb_queue_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue_name` VARCHAR(50) NOT NULL,
  `is_paused` TINYINT(1) NOT NULL DEFAULT 0,
  `max_workers` INT(11) NOT NULL DEFAULT 5,
  `timeout` INT(11) NOT NULL DEFAULT 300 COMMENT 'Timeout dalam detik',
  `retry_delay` INT(11) NOT NULL DEFAULT 60 COMMENT 'Delay retry dalam detik',
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_name` (`queue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 22. QUEUE: tb_workers (Active Workers Tracking)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_workers`;
CREATE TABLE `tb_workers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` VARCHAR(100) NOT NULL COMMENT 'Unique ID worker',
  `queue_name` VARCHAR(50) NOT NULL,
  `pid` INT(11) DEFAULT NULL COMMENT 'Process ID',
  `hostname` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('idle', 'busy', 'stopped') NOT NULL DEFAULT 'idle',
  `current_job_id` VARCHAR(100) DEFAULT NULL,
  `last_heartbeat` DATETIME DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `worker_id` (`worker_id`),
  KEY `queue_name` (`queue_name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 23. QUEUE: tb_queue_counters (Queue Statistics)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_queue_counters`;
CREATE TABLE `tb_queue_counters` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue_name` VARCHAR(50) NOT NULL,
  `date` DATE NOT NULL,
  `total_jobs` BIGINT(20) NOT NULL DEFAULT 0,
  `completed_jobs` BIGINT(20) NOT NULL DEFAULT 0,
  `failed_jobs` BIGINT(20) NOT NULL DEFAULT 0,
  `avg_processing_time` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Rata-rata waktu proses dalam detik',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_date_unique` (`queue_name`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 24. LAPORAN: laporan_jobs (Report Generation Jobs)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `laporan_jobs`;
CREATE TABLE `laporan_jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `jenis_laporan` ENUM('pdf', 'excel') NOT NULL COMMENT 'Jenis laporan: pdf atau excel',
  `unit_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Filter berdasarkan unit (NULL = semua unit)',
  `periode_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Filter berdasarkan periode (NULL = semua periode)',
  `tanggal_mulai` DATE DEFAULT NULL COMMENT 'Tanggal mulai periode laporan',
  `tanggal_selesai` DATE DEFAULT NULL COMMENT 'Tanggal selesai periode laporan',
  `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'Status job queue',
  `file_path` VARCHAR(500) DEFAULT NULL COMMENT 'Path file hasil generate',
  `file_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nama file asli',
  `error_message` TEXT DEFAULT NULL COMMENT 'Pesan error jika gagal',
  `progress` TINYINT(3) NOT NULL DEFAULT 0 COMMENT 'Progress percentage (0-100)',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL COMMENT 'Waktu penyelesaian job',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `user_status` (`user_id`, `status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_laporan_user` FOREIGN KEY (`user_id`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE,
  CONSTRAINT `fk_laporan_unit` FOREIGN KEY (`unit_id`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE SET NULL,
  CONSTRAINT `fk_laporan_periode` FOREIGN KEY (`periode_id`) REFERENCES `tb_periode` (`id_periode`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 25. API: tb_api_keys (API Key Management)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_api_keys`;
CREATE TABLE `tb_api_keys` (
  `id_api_key` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_unit` INT(11) UNSIGNED DEFAULT NULL,
  `key_name` VARCHAR(255) NOT NULL,
  `api_key` VARCHAR(255) NOT NULL,
  `api_secret` VARCHAR(255) DEFAULT NULL,
  `permissions` TEXT DEFAULT NULL COMMENT 'JSON array of allowed endpoints/actions',
  `rate_limit` INT(11) NOT NULL DEFAULT 100 COMMENT 'Requests per minute',
  `last_used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `revoked_at` DATETIME DEFAULT NULL,
  `revoked_reason` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_api_key`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `is_active` (`is_active`),
  KEY `unit_active` (`id_unit`, `is_active`),
  CONSTRAINT `fk_api_unit` FOREIGN KEY (`id_unit`) REFERENCES `tb_unit_layanan` (`id_unit`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TRIGGERS untuk created_at dan updated_at
-- ============================================================================

DELIMITER $$

-- Trigger untuk tb_instansi
CREATE TRIGGER `trg_tb_instansi_before_insert` BEFORE INSERT ON `tb_instansi` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_instansi_before_update` BEFORE UPDATE ON `tb_instansi` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_unit_layanan
CREATE TRIGGER `trg_tb_unit_layanan_before_insert` BEFORE INSERT ON `tb_unit_layanan` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_unit_layanan_before_update` BEFORE UPDATE ON `tb_unit_layanan` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_periode
CREATE TRIGGER `trg_tb_periode_before_insert` BEFORE INSERT ON `tb_periode` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_periode_before_update` BEFORE UPDATE ON `tb_periode` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_pengguna
CREATE TRIGGER `trg_tb_pengguna_before_insert` BEFORE INSERT ON `tb_pengguna` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_pengguna_before_update` BEFORE UPDATE ON `tb_pengguna` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_kuesioner
CREATE TRIGGER `trg_tb_kuesioner_before_insert` BEFORE INSERT ON `tb_kuesioner` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_kuesioner_before_update` BEFORE UPDATE ON `tb_kuesioner` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_responden
CREATE TRIGGER `trg_tb_responden_before_insert` BEFORE INSERT ON `tb_responden` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_responden_before_update` BEFORE UPDATE ON `tb_responden` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_saran
CREATE TRIGGER `trg_tb_saran_before_insert` BEFORE INSERT ON `tb_saran` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_saran_before_update` BEFORE UPDATE ON `tb_saran` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_rekap_ikm
CREATE TRIGGER `trg_tb_rekap_ikm_before_insert` BEFORE INSERT ON `tb_rekap_ikm` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_rekap_ikm_before_update` BEFORE UPDATE ON `tb_rekap_ikm` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_notifikasi
CREATE TRIGGER `trg_tb_notifikasi_before_insert` BEFORE INSERT ON `tb_notifikasi` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_notifikasi_before_update` BEFORE UPDATE ON `tb_notifikasi` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_backup_log
CREATE TRIGGER `trg_tb_backup_log_before_insert` BEFORE INSERT ON `tb_backup_log` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
END$$

-- Trigger untuk tb_bahasa
CREATE TRIGGER `trg_tb_bahasa_before_insert` BEFORE INSERT ON `tb_bahasa` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_bahasa_before_update` BEFORE UPDATE ON `tb_bahasa` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_qr_code
CREATE TRIGGER `trg_tb_qr_code_before_insert` BEFORE INSERT ON `tb_qr_code` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_qr_code_before_update` BEFORE UPDATE ON `tb_qr_code` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_queue_jobs
CREATE TRIGGER `trg_tb_queue_jobs_before_insert` BEFORE INSERT ON `tb_queue_jobs` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_queue_jobs_before_update` BEFORE UPDATE ON `tb_queue_jobs` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger untuk tb_api_keys
CREATE TRIGGER `trg_tb_api_keys_before_insert` BEFORE INSERT ON `tb_api_keys` FOR EACH ROW
BEGIN
    IF NEW.created_at IS NULL THEN SET NEW.created_at = NOW(); END IF;
    IF NEW.updated_at IS NULL THEN SET NEW.updated_at = NOW(); END IF;
END$$

CREATE TRIGGER `trg_tb_api_keys_before_update` BEFORE UPDATE ON `tb_api_keys` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

DELIMITER ;

-- ============================================================================
-- DATA CONTOH LENGKAP (SEED DATA)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. DATA INSTANSI
-- ----------------------------------------------------------------------------
INSERT INTO `tb_instansi` (`nama_instansi`, `kode_instansi`, `alamat`, `kota`, `provinsi`, `kode_pos`, `telepon`, `fax`, `email`, `website`, `kepala_instansi`, `nip_kepala`, `visi`, `misi`) VALUES
('Pemerintah Kota Jakarta Pusat', 'PEMKOT-JKT-PUSAT', 'Jl. Tanah Abang I No.1', 'Jakarta Pusat', 'DKI Jakarta', '10160', '(021) 3841024', '(021) 3841025', 'info@jakartapusat.go.id', 'https://jakartapusat.go.id', 'Dr. H. Muhammad Anwar, M.Si', '196805151990031005', 'Mewujudkan Jakarta Pusat sebagai pusat bisnis dan budaya yang modern dan berkelanjutan', '1. Meningkatkan kualitas pelayanan publik\n2. Mengembangkan infrastruktur kota\n3. Melestarikan budaya dan sejarah kota\n4. Meningkatkan kesejahteraan masyarakat'),
('Pemerintah Kota Jakarta Selatan', 'PEMKOT-JKT-SELATAN', 'Jl. Fatmawati Raya No.1', 'Jakarta Selatan', 'DKI Jakarta', '12430', '(021) 7501024', '(021) 7501025', 'info@jakartaselatan.go.id', 'https://jakartaselatan.go.id', 'Ir. H. Budiman, M.T', '196907201992031006', 'Jakarta Selatan Maju, Sejahtera dan Berbudaya', '1. Pelayanan prima kepada masyarakat\n2. Pembangunan berkelanjutan\n3. Pelestarian lingkungan hidup');

-- ----------------------------------------------------------------------------
-- 2. DATA UNIT LAYANAN
-- ----------------------------------------------------------------------------
INSERT INTO `tb_unit_layanan` (`id_instansi`, `nama_unit`, `kode_unit`, `alamat`, `kepala_unit`, `email`, `telepon`, `logo_path`) VALUES
(1, 'Dinas Kependudukan dan Pencatatan Sipil', 'DISDUKCAPIL-001', 'Jl. Pelayanan Publik No. 1, Jakarta Pusat', 'Dr. Ahmad Wijaya, M.Si', 'info@disdukcapil.jakpus.go.id', '021-12345678', '/assets/logos/disdukcapil.png'),
(1, 'Dinas Kesehatan', 'DINKES-001', 'Jl. Sehat Sentosa No. 45, Jakarta Selatan', 'dr. Siti Nurhaliza, M.Kes', 'info@dinkes.jakpus.go.id', '021-87654321', '/assets/logos/dinkes.png'),
(1, 'Dinas Pendidikan', 'DIKNAS-001', 'Jl. Pendidikan No. 12, Jakarta Pusat', 'Drs. Hendra Gunawan, M.Pd', 'info@diknas.jakpus.go.id', '021-34567890', '/assets/logos/diknas.png'),
(2, 'Dinas Kependudukan dan Pencatatan Sipil', 'DISDUKCAPIL-002', 'Jl. Fatmawati No. 88, Jakarta Selatan', 'Dewi Sartika, S.Sos, M.Si', 'info@disdukcapil.jaksel.go.id', '021-76543210', '/assets/logos/disdukcapil.png'),
(2, 'Dinas Kesehatan', 'DINKES-002', 'Jl. Kesehatan No. 23, Jakarta Selatan', 'dr. Bambang Sutrisno, M.Kes', 'info@dinkes.jaksel.go.id', '021-65432109', '/assets/logos/dinkes.png');

-- ----------------------------------------------------------------------------
-- 3. DATA PERIODE
-- ----------------------------------------------------------------------------
INSERT INTO `tb_periode` (`nama_periode`, `tahun`, `bulan_mulai`, `bulan_selesai`, `is_active`, `is_locked`) VALUES
('Triwulan I Tahun 2024', 2024, 1, 3, 1, 0),
('Triwulan II Tahun 2024', 2024, 4, 6, 0, 0),
('Triwulan III Tahun 2024', 2024, 7, 9, 0, 0),
('Triwulan IV Tahun 2024', 2024, 10, 12, 0, 0),
('Triwulan I Tahun 2025', 2025, 1, 3, 0, 0);

-- ----------------------------------------------------------------------------
-- 4. DATA PENGGUNA (Password default: Admin@123!)
-- Hash password dihasilkan menggunakan PHP password_hash() dengan PASSWORD_DEFAULT
-- ----------------------------------------------------------------------------
INSERT INTO `tb_pengguna` (`id_unit`, `username`, `password_hash`, `nama_lengkap`, `email`, `role`, `mfa_enabled`) VALUES
(NULL, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'superadmin@ikm.go.id', 'super_admin', 0),
(1, 'admin_disdukcapil', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Disdukcapil', 'admin@disdukcapil.jakpus.go.id', 'admin_unit', 0),
(1, 'operator_disdukcapil', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Disdukcapil', 'operator@disdukcapil.jakpus.go.id', 'operator', 0),
(2, 'admin_dinkes', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Dinkes', 'admin@dinkes.jakpus.go.id', 'admin_unit', 0),
(3, 'admin_diknas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Diknas', 'admin@diknas.jakpus.go.id', 'admin_unit', 0),
(4, 'admin_disdukcapil_selatan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Disdukcapil Jaksel', 'admin@disdukcapil.jaksel.go.id', 'admin_unit', 0),
(5, 'admin_dinkes_selatan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Dinkes Jaksel', 'admin@dinkes.jaksel.go.id', 'admin_unit', 0),
(1, 'viewer_disdukcapil', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Disdukcapil', 'viewer@disdukcapil.jakpus.go.id', 'viewer', 0);

-- ----------------------------------------------------------------------------
-- 5. DATA KUESIONER (9 Unsur Wajib IKM - PermenPANRB 14/2017)
-- ----------------------------------------------------------------------------
INSERT INTO `tb_kuesioner` (`id_unit`, `unsur_code`, `nama_unsur`, `deskripsi`, `bobot`, `is_active`, `urutan`) VALUES
-- Unit 1: Disdukcapil Jakarta Pusat
(1, 'U1', 'Persyaratan Pelayanan', 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku', 1.00, 1, 1),
(1, 'U2', 'Prosedur Pelayanan', 'Alur/prosedur pelayanan yang jelas dan mudah dipahami', 1.00, 1, 2),
(1, 'U3', 'Waktu Pelayanan', 'Ketepatan waktu penyelesaian dokumen/layanan', 1.00, 1, 3),
(1, 'U4', 'Biaya/Tarif', 'Besaran biaya/tarif yang transparan dan sesuai ketentuan', 1.00, 1, 4),
(1, 'U5', 'Produk Spesifikasi Jenis Pelayanan', 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan', 1.00, 1, 5),
(1, 'U6', 'Kompetensi Pelaksana', 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan', 1.00, 1, 6),
(1, 'U7', 'Perilaku Pelaksana', 'Sikap dan perilaku petugas dalam memberikan pelayanan', 1.00, 1, 7),
(1, 'U8', 'Penanganan Pengaduan, Saran dan Masukan', 'Respon terhadap pengaduan, saran dan masukan masyarakat', 1.00, 1, 8),
(1, 'U9', 'Sarana dan Prasarana', 'Ketersediaan dan kondisi sarana prasarana pelayanan', 1.00, 1, 9),
-- Unit 2: Dinkes Jakarta Pusat
(2, 'U1', 'Persyaratan Pelayanan', 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku', 1.00, 1, 1),
(2, 'U2', 'Prosedur Pelayanan', 'Alur/prosedur pelayanan yang jelas dan mudah dipahami', 1.00, 1, 2),
(2, 'U3', 'Waktu Pelayanan', 'Ketepatan waktu penyelesaian dokumen/layanan', 1.00, 1, 3),
(2, 'U4', 'Biaya/Tarif', 'Besaran biaya/tarif yang transparan dan sesuai ketentuan', 1.00, 1, 4),
(2, 'U5', 'Produk Spesifikasi Jenis Pelayanan', 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan', 1.00, 1, 5),
(2, 'U6', 'Kompetensi Pelaksana', 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan', 1.00, 1, 6),
(2, 'U7', 'Perilaku Pelaksana', 'Sikap dan perilaku petugas dalam memberikan pelayanan', 1.00, 1, 7),
(2, 'U8', 'Penanganan Pengaduan, Saran dan Masukan', 'Respon terhadap pengaduan, saran dan masukan masyarakat', 1.00, 1, 8),
(2, 'U9', 'Sarana dan Prasarana', 'Ketersediaan dan kondisi sarana prasarana pelayanan', 1.00, 1, 9),
-- Unit 3: Diknas Jakarta Pusat
(3, 'U1', 'Persyaratan Pelayanan', 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku', 1.00, 1, 1),
(3, 'U2', 'Prosedur Pelayanan', 'Alur/prosedur pelayanan yang jelas dan mudah dipahami', 1.00, 1, 2),
(3, 'U3', 'Waktu Pelayanan', 'Ketepatan waktu penyelesaian dokumen/layanan', 1.00, 1, 3),
(3, 'U4', 'Biaya/Tarif', 'Besaran biaya/tarif yang transparan dan sesuai ketentuan', 1.00, 1, 4),
(3, 'U5', 'Produk Spesifikasi Jenis Pelayanan', 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan', 1.00, 1, 5),
(3, 'U6', 'Kompetensi Pelaksana', 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan', 1.00, 1, 6),
(3, 'U7', 'Perilaku Pelaksana', 'Sikap dan perilaku petugas dalam memberikan pelayanan', 1.00, 1, 7),
(3, 'U8', 'Penanganan Pengaduan, Saran dan Masukan', 'Respon terhadap pengaduan, saran dan masukan masyarakat', 1.00, 1, 8),
(3, 'U9', 'Sarana dan Prasarana', 'Ketersediaan dan kondisi sarana prasarana pelayanan', 1.00, 1, 9),
-- Unit 4: Disdukcapil Jakarta Selatan
(4, 'U1', 'Persyaratan Pelayanan', 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku', 1.00, 1, 1),
(4, 'U2', 'Prosedur Pelayanan', 'Alur/prosedur pelayanan yang jelas dan mudah dipahami', 1.00, 1, 2),
(4, 'U3', 'Waktu Pelayanan', 'Ketepatan waktu penyelesaian dokumen/layanan', 1.00, 1, 3),
(4, 'U4', 'Biaya/Tarif', 'Besaran biaya/tarif yang transparan dan sesuai ketentuan', 1.00, 1, 4),
(4, 'U5', 'Produk Spesifikasi Jenis Pelayanan', 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan', 1.00, 1, 5),
(4, 'U6', 'Kompetensi Pelaksana', 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan', 1.00, 1, 6),
(4, 'U7', 'Perilaku Pelaksana', 'Sikap dan perilaku petugas dalam memberikan pelayanan', 1.00, 1, 7),
(4, 'U8', 'Penanganan Pengaduan, Saran dan Masukan', 'Respon terhadap pengaduan, saran dan masukan masyarakat', 1.00, 1, 8),
(4, 'U9', 'Sarana dan Prasarana', 'Ketersediaan dan kondisi sarana prasarana pelayanan', 1.00, 1, 9),
-- Unit 5: Dinkes Jakarta Selatan
(5, 'U1', 'Persyaratan Pelayanan', 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku', 1.00, 1, 1),
(5, 'U2', 'Prosedur Pelayanan', 'Alur/prosedur pelayanan yang jelas dan mudah dipahami', 1.00, 1, 2),
(5, 'U3', 'Waktu Pelayanan', 'Ketepatan waktu penyelesaian dokumen/layanan', 1.00, 1, 3),
(5, 'U4', 'Biaya/Tarif', 'Besaran biaya/tarif yang transparan dan sesuai ketentuan', 1.00, 1, 4),
(5, 'U5', 'Produk Spesifikasi Jenis Pelayanan', 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan', 1.00, 1, 5),
(5, 'U6', 'Kompetensi Pelaksana', 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan', 1.00, 1, 6),
(5, 'U7', 'Perilaku Pelaksana', 'Sikap dan perilaku petugas dalam memberikan pelayanan', 1.00, 1, 7),
(5, 'U8', 'Penanganan Pengaduan, Saran dan Masukan', 'Respon terhadap pengaduan, saran dan masukan masyarakat', 1.00, 1, 8),
(5, 'U9', 'Sarana dan Prasarana', 'Ketersediaan dan kondisi sarana prasarana pelayanan', 1.00, 1, 9);

-- ----------------------------------------------------------------------------
-- 6. DATA BAHASA (Multi-language Support)
-- ----------------------------------------------------------------------------
INSERT INTO `tb_bahasa` (`locale`, `module`, `key`, `value`, `is_active`) VALUES
('id', 'app', 'welcome', 'Selamat Datang', 1),
('id', 'app', 'login', 'Masuk', 1),
('id', 'app', 'logout', 'Keluar', 1),
('id', 'app', 'dashboard', 'Dasbor', 1),
('id', 'app', 'survey', 'Survei', 1),
('id', 'app', 'submit', 'Kirim', 1),
('id', 'app', 'cancel', 'Batal', 1),
('id', 'app', 'save', 'Simpan', 1),
('id', 'app', 'delete', 'Hapus', 1),
('id', 'app', 'edit', 'Ubah', 1),
('id', 'app', 'view', 'Lihat', 1),
('id', 'app', 'search', 'Cari', 1),
('id', 'app', 'filter', 'Filter', 1),
('id', 'app', 'export', 'Ekspor', 1),
('id', 'app', 'import', 'Impor', 1),
('id', 'app', 'settings', 'Pengaturan', 1),
('id', 'app', 'profile', 'Profil', 1),
('id', 'app', 'help', 'Bantuan', 1),
('id', 'app', 'yes', 'Ya', 1),
('id', 'app', 'no', 'Tidak', 1),
('id', 'app', 'success', 'Berhasil', 1),
('id', 'app', 'error', 'Gagal', 1),
('id', 'app', 'warning', 'Peringatan', 1),
('id', 'app', 'info', 'Informasi', 1),
('en', 'app', 'welcome', 'Welcome', 1),
('en', 'app', 'login', 'Login', 1),
('en', 'app', 'logout', 'Logout', 1),
('en', 'app', 'dashboard', 'Dashboard', 1),
('en', 'app', 'survey', 'Survey', 1),
('en', 'app', 'submit', 'Submit', 1),
('en', 'app', 'cancel', 'Cancel', 1),
('en', 'app', 'save', 'Save', 1),
('en', 'app', 'delete', 'Delete', 1),
('en', 'app', 'edit', 'Edit', 1),
('en', 'app', 'view', 'View', 1),
('en', 'app', 'search', 'Search', 1),
('en', 'app', 'filter', 'Filter', 1),
('en', 'app', 'export', 'Export', 1),
('en', 'app', 'import', 'Import', 1),
('en', 'app', 'settings', 'Settings', 1),
('en', 'app', 'profile', 'Profile', 1),
('en', 'app', 'help', 'Help', 1),
('en', 'app', 'yes', 'Yes', 1),
('en', 'app', 'no', 'No', 1),
('en', 'app', 'success', 'Success', 1),
('en', 'app', 'error', 'Error', 1),
('en', 'app', 'warning', 'Warning', 1),
('en', 'app', 'info', 'Information', 1);

-- ----------------------------------------------------------------------------
-- 7. DATA TRANSLATIONS
-- ----------------------------------------------------------------------------
INSERT INTO `translations` (`key`, `locale`, `translation`, `context`) VALUES
('skip_to_main_content', 'id', 'Langsung ke konten utama', 'Accessibility'),
('main_navigation', 'id', 'Navigasi utama', 'Accessibility'),
('toggle_navigation', 'id', 'Buka/tutup navigasi', 'Accessibility'),
('language_selection', 'id', 'Pilihan bahasa', 'Accessibility'),
('page_title', 'id', 'Survei Kepuasan Masyarakat', 'SEO'),
('page_description', 'id', 'Survei Kepuasan Masyarakat untuk meningkatkan kualitas layanan publik', 'SEO'),
('page_keywords', 'id', 'IKM, survei, kepuasan masyarakat, layanan publik', 'SEO'),
('default_instansi_name', 'id', 'Pemerintah Daerah', 'Default'),
('default_instansi_description', 'id', 'Melayani masyarakat dengan sepenuh hati', 'Default'),
('dashboard', 'id', 'Dashboard', 'Navigation'),
('home', 'id', 'Beranda', 'Navigation'),
('surveys', 'id', 'Survei', 'Navigation'),
('welcome_message', 'id', 'Kami menghargai pendapat Anda untuk meningkatkan kualitas layanan kami', 'Hero'),
('available_services', 'id', 'Unit Layanan Tersedia', 'Services'),
('no_active_services', 'id', 'Belum ada unit layanan yang aktif saat ini.', 'Services'),
('fill_survey', 'id', 'Isi Survei', 'Action'),
('fill_survey_for', 'id', 'Isi survei untuk', 'Action'),
('transparency_dashboard', 'id', 'Dashboard Transparansi IKM', 'Dashboard'),
('view_ikm_results', 'id', 'Lihat hasil Indeks Kepuasan Masyarakat secara transparan', 'Dashboard'),
('go_to_dashboard', 'id', 'Ke Dashboard', 'Action'),
('total_respondents', 'id', 'Total Responden', 'Stats'),
('average_score', 'id', 'Nilai Rata-rata', 'Stats'),
('satisfaction_rate', 'id', 'Tingkat Kepuasan', 'Stats'),
('back_to_home', 'id', 'Kembali ke Beranda', 'Action'),
('quick_links', 'id', 'Tautan Cepat', 'Footer'),
('accessibility', 'id', 'Aksesibilitas', 'Footer'),
('privacy_policy', 'id', 'Kebijakan Privasi', 'Footer'),
('all_rights_reserved', 'id', 'Hak Cipta Dilindungi', 'Footer'),
('skip_to_main_content', 'en', 'Skip to main content', 'Accessibility'),
('main_navigation', 'en', 'Main navigation', 'Accessibility'),
('toggle_navigation', 'en', 'Toggle navigation', 'Accessibility'),
('language_selection', 'en', 'Language selection', 'Accessibility'),
('page_title', 'en', 'Public Satisfaction Survey', 'SEO'),
('page_description', 'en', 'Public Satisfaction Survey to improve public service quality', 'SEO'),
('page_keywords', 'en', 'IKM, survey, public satisfaction, public service', 'SEO'),
('default_instansi_name', 'en', 'Regional Government', 'Default'),
('default_instansi_description', 'en', 'Serving the community with all our heart', 'Default'),
('dashboard', 'en', 'Dashboard', 'Navigation'),
('home', 'en', 'Home', 'Navigation'),
('surveys', 'en', 'Surveys', 'Navigation'),
('welcome_message', 'en', 'We value your feedback to improve our service quality', 'Hero'),
('available_services', 'en', 'Available Services', 'Services'),
('no_active_services', 'en', 'No active service units at this time.', 'Services'),
('fill_survey', 'en', 'Fill Survey', 'Action'),
('fill_survey_for', 'en', 'Fill survey for', 'Action'),
('transparency_dashboard', 'en', 'IKM Transparency Dashboard', 'Dashboard'),
('view_ikm_results', 'en', 'View Public Satisfaction Index results transparently', 'Dashboard'),
('go_to_dashboard', 'en', 'Go to Dashboard', 'Action'),
('total_respondents', 'en', 'Total Respondents', 'Stats'),
('average_score', 'en', 'Average Score', 'Stats'),
('satisfaction_rate', 'en', 'Satisfaction Rate', 'Stats'),
('back_to_home', 'en', 'Back to Home', 'Action'),
('quick_links', 'en', 'Quick Links', 'Footer'),
('accessibility', 'en', 'Accessibility', 'Footer'),
('privacy_policy', 'en', 'Privacy Policy', 'Footer'),
('all_rights_reserved', 'en', 'All Rights Reserved', 'Footer');

-- ----------------------------------------------------------------------------
-- 8. DATA RESPONDEN CONTOH
-- ----------------------------------------------------------------------------
INSERT INTO `tb_responden` (`id_unit`, `id_periode`, `nik`, `nama`, `jenis_kelamin`, `usia`, `pendidikan`, `pekerjaan`, `email`, `telepon`, `consent_given`, `consent_timestamp`, `consent_version`, `ip_address`, `user_agent`) VALUES
(1, 1, '3171012345670001', 'Budi Santoso', 'L', 35, 'S1', 'Wiraswasta', 'budi.santoso@email.com', '081234567890', 1, NOW(), '1.0', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 1, '3171023456780002', 'Siti Aminah', 'P', 28, 'SMA', 'Ibu Rumah Tangga', 'siti.aminah@email.com', '081234567891', 1, NOW(), '1.0', '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)'),
(1, 1, '3171034567890003', 'Ahmad Fauzi', 'L', 42, 'S2', 'PNS', 'ahmad.fauzi@email.com', '081234567892', 1, NOW(), '1.0', '192.168.1.102', 'Mozilla/5.0 (Android 11; Mobile)'),
(1, 1, '3171045678900004', 'Dewi Lestari', 'P', 31, 'S1', 'Karyawan Swasta', 'dewi.lestari@email.com', '081234567893', 1, NOW(), '1.0', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 1, '3171056789010005', 'Eko Prasetyo', 'L', 45, 'SMA', 'Pedagang', 'eko.prasetyo@email.com', '081234567894', 1, NOW(), '1.0', '192.168.1.104', 'Mozilla/5.0 (Linux; Android 10)'),
(2, 1, '3172012345670006', 'Fitri Handayani', 'P', 29, 'S1', 'Tenaga Kesehatan', 'fitri.handayani@email.com', '081345678901', 1, NOW(), '1.0', '192.168.2.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(2, 1, '3172023456780007', 'Gunawan Wibowo', 'L', 38, 'D3', 'Teknisi', 'gunawan.wibowo@email.com', '081345678902', 1, NOW(), '1.0', '192.168.2.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)'),
(2, 1, '3172034567890008', 'Hesti Wulandari', 'P', 33, 'S1', 'Apoteker', 'hesti.wulandari@email.com', '081345678903', 1, NOW(), '1.0', '192.168.2.102', 'Mozilla/5.0 (Android 12; Mobile)'),
(3, 1, '3173012345670009', 'Indra Kusuma', 'L', 40, 'S2', 'Guru', 'indra.kusuma@email.com', '081456789012', 1, NOW(), '1.0', '192.168.3.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(3, 1, '3173023456780010', 'Julia Rahayu', 'P', 36, 'S1', 'Staf Administrasi', 'julia.rahayu@email.com', '081456789013', 1, NOW(), '1.0', '192.168.3.101', 'Mozilla/5.0 (iPad; CPU OS 14_0)');

-- ----------------------------------------------------------------------------
-- 9. DATA JAWABAN SURVEI CONTOH
-- Untuk setiap responden, berikan nilai 1-4 untuk 9 unsur
-- Skala: 1=Kurang, 2=Cukup, 3=Baik, 4=Sangat Baik
-- ----------------------------------------------------------------------------

-- Responden 1 (Budi Santoso) - Unit 1, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(1, 1, 1, 1, 4, NOW()), (1, 2, 1, 1, 4, NOW()), (1, 3, 1, 1, 3, NOW()), (1, 4, 1, 1, 4, NOW()), (1, 5, 1, 1, 4, NOW()), (1, 6, 1, 1, 4, NOW()), (1, 7, 1, 1, 4, NOW()), (1, 8, 1, 1, 3, NOW()), (1, 9, 1, 1, 4, NOW());

-- Responden 2 (Siti Aminah) - Unit 1, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(2, 1, 1, 1, 3, NOW()), (2, 2, 1, 1, 4, NOW()), (2, 3, 1, 1, 4, NOW()), (2, 4, 1, 1, 4, NOW()), (2, 5, 1, 1, 3, NOW()), (2, 6, 1, 1, 4, NOW()), (2, 7, 1, 1, 4, NOW()), (2, 8, 1, 1, 4, NOW()), (2, 9, 1, 1, 3, NOW());

-- Responden 3 (Ahmad Fauzi) - Unit 1, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(3, 1, 1, 1, 4, NOW()), (3, 2, 1, 1, 4, NOW()), (3, 3, 1, 1, 4, NOW()), (3, 4, 1, 1, 3, NOW()), (3, 5, 1, 1, 4, NOW()), (3, 6, 1, 1, 3, NOW()), (3, 7, 1, 1, 4, NOW()), (3, 8, 1, 1, 4, NOW()), (3, 9, 1, 1, 4, NOW());

-- Responden 4 (Dewi Lestari) - Unit 1, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(4, 1, 1, 1, 3, NOW()), (4, 2, 1, 1, 3, NOW()), (4, 3, 1, 1, 4, NOW()), (4, 4, 1, 1, 4, NOW()), (4, 5, 1, 1, 4, NOW()), (4, 6, 1, 1, 4, NOW()), (4, 7, 1, 1, 3, NOW()), (4, 8, 1, 1, 4, NOW()), (4, 9, 1, 1, 4, NOW());

-- Responden 5 (Eko Prasetyo) - Unit 1, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(5, 1, 1, 1, 4, NOW()), (5, 2, 1, 1, 3, NOW()), (5, 3, 1, 1, 3, NOW()), (5, 4, 1, 1, 4, NOW()), (5, 5, 1, 1, 3, NOW()), (5, 6, 1, 1, 4, NOW()), (5, 7, 1, 1, 4, NOW()), (5, 8, 1, 1, 3, NOW()), (5, 9, 1, 1, 3, NOW());

-- Responden 6 (Fitri Handayani) - Unit 2, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(6, 10, 1, 2, 4, NOW()), (6, 11, 1, 2, 4, NOW()), (6, 12, 1, 2, 4, NOW()), (6, 13, 1, 2, 3, NOW()), (6, 14, 1, 2, 4, NOW()), (6, 15, 1, 2, 4, NOW()), (6, 16, 1, 2, 4, NOW()), (6, 17, 1, 2, 4, NOW()), (6, 18, 1, 2, 3, NOW());

-- Responden 7 (Gunawan Wibowo) - Unit 2, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(7, 10, 1, 2, 3, NOW()), (7, 11, 1, 2, 4, NOW()), (7, 12, 1, 2, 3, NOW()), (7, 13, 1, 2, 4, NOW()), (7, 14, 1, 2, 4, NOW()), (7, 15, 1, 2, 3, NOW()), (7, 16, 1, 2, 4, NOW()), (7, 17, 1, 2, 4, NOW()), (7, 18, 1, 2, 4, NOW());

-- Responden 8 (Hesti Wulandari) - Unit 2, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(8, 10, 1, 2, 4, NOW()), (8, 11, 1, 2, 3, NOW()), (8, 12, 1, 2, 4, NOW()), (8, 13, 1, 2, 4, NOW()), (8, 14, 1, 2, 3, NOW()), (8, 15, 1, 2, 4, NOW()), (8, 16, 1, 2, 3, NOW()), (8, 17, 1, 2, 4, NOW()), (8, 18, 1, 2, 4, NOW());

-- Responden 9 (Indra Kusuma) - Unit 3, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(9, 19, 1, 3, 4, NOW()), (9, 20, 1, 3, 4, NOW()), (9, 21, 1, 3, 3, NOW()), (9, 22, 1, 3, 4, NOW()), (9, 23, 1, 3, 4, NOW()), (9, 24, 1, 3, 4, NOW()), (9, 25, 1, 3, 4, NOW()), (9, 26, 1, 3, 3, NOW()), (9, 27, 1, 3, 4, NOW());

-- Responden 10 (Julia Rahayu) - Unit 3, Periode 1
INSERT INTO `tb_survei_jawaban` (`id_responden`, `id_kuesioner`, `id_periode`, `id_unit`, `nilai`, `created_at`) VALUES
(10, 19, 1, 3, 3, NOW()), (10, 20, 1, 3, 4, NOW()), (10, 21, 1, 3, 4, NOW()), (10, 22, 1, 3, 3, NOW()), (10, 23, 1, 3, 4, NOW()), (10, 24, 1, 3, 4, NOW()), (10, 25, 1, 3, 3, NOW()), (10, 26, 1, 3, 4, NOW()), (10, 27, 1, 3, 4, NOW());

-- ----------------------------------------------------------------------------
-- 10. DATA SARAN DAN MASUKAN
-- ----------------------------------------------------------------------------
INSERT INTO `tb_saran` (`id_responden`, `id_unit`, `pesan`, `status`, `tanggapan`, `ditanggapi_oleh`, `ditanggapi_pada`, `created_at`) VALUES
(1, 1, 'Pelayanan sudah sangat baik, namun antrian masih cukup panjang. Mohon ditambah loket pelayanan.', 'ditindaklanjuti', 'Terima kasih atas masukannya. Kami akan menambah 2 loket pelayanan starting bulan depan.', 2, NOW(), NOW()),
(2, 1, 'Petugas sangat ramah dan membantu. Terima kasih!', 'ditindaklanjuti', 'Terima kasih atas apresiasi Bapak/Ibu. Ini menjadi motivasi kami untuk terus meningkatkan pelayanan.', 2, NOW(), NOW()),
(3, 1, 'Mohon sistem online diperbaiki karena sering error saat upload dokumen.', 'diproses', 'Tim IT kami sedang melakukan perbaikan sistem. Diharapkan selesai dalam 1 minggu.', 2, NOW(), NOW()),
(6, 2, 'Ruang tunggu perlu ditingkatkan kenyamanannya, AC kurang dingin.', 'baru', NULL, NULL, NULL, NOW()),
(7, 2, 'Alur pelayanan sudah jelas dan cepat. Sangat memuaskan!', 'ditindaklanjuti', 'Terima kasih atas feedback positifnya. Kami akan pertahankan kualitas pelayanan ini.', 4, NOW(), NOW()),
(9, 3, 'Informasi persyaratan sebaiknya lebih detail di website.', 'diproses', 'Kami sedang update website dengan informasi lebih lengkap. Terima kasih sarannya.', 5, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 11. DATA REKAP IKM (Hasil Perhitungan)
-- Rumus: Nilai IKM = (Total Nilai / (Jumlah Responden x 9 Unsur x 4)) x 100
-- Mutu: A (90-100), B (70-89), C (50-69), D (<50)
-- ----------------------------------------------------------------------------
INSERT INTO `tb_rekap_ikm` (`id_unit`, `id_periode`, `jumlah_responden`, `nilai_ikm_mentah`, `nilai_ikm_final`, `mutu_layanan`, `delta_ikm`, `flag_alert`, `is_published`, `published_at`, `published_by`) VALUES
-- Unit 1: 5 responden x 9 unsur = 45 jawaban
-- Total nilai: (4+4+3+4+4+4+4+3+4) + (3+4+4+4+3+4+4+4+3) + (4+4+4+3+4+3+4+4+4) + (3+3+4+4+4+4+3+4+4) + (4+3+3+4+3+4+4+3+3) = 34+33+34+33+31 = 165
-- Nilai mentah: 165/45 = 3.67
-- Nilai final: 3.67 x 25 = 91.67 (Mutu A)
(1, 1, 5, 3.67, 91.67, 'A', NULL, 0, 1, NOW(), 1),
-- Unit 2: 3 responden x 9 unsur = 27 jawaban
-- Total nilai: (4+4+4+3+4+4+4+4+3) + (3+4+3+4+4+3+4+4+4) + (4+3+4+4+3+4+3+4+4) = 34+33+33 = 100
-- Nilai mentah: 100/27 = 3.70
-- Nilai final: 3.70 x 25 = 92.59 (Mutu A)
(2, 1, 3, 3.70, 92.59, 'A', NULL, 0, 1, NOW(), 1),
-- Unit 3: 2 responden x 9 unsur = 18 jawaban
-- Total nilai: (4+4+3+4+4+4+4+3+4) + (3+4+4+3+4+4+3+4+4) = 34+33 = 67
-- Nilai mentah: 67/18 = 3.72
-- Nilai final: 3.72 x 25 = 93.06 (Mutu A)
(3, 1, 2, 3.72, 93.06, 'A', NULL, 0, 1, NOW(), 1);

-- ----------------------------------------------------------------------------
-- 12. DATA NOTIFIKASI
-- ----------------------------------------------------------------------------
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `channel`, `is_read`, `data`, `sent_at`) VALUES
(1, 'Laporan IKM Triwulan I Siap', 'Laporan IKM Triwulan I Tahun 2024 telah selesai dibuat dan siap untuk dipublikasikan.', 'success', 'inapp', 0, '{"survey_id": 1, "url": "/laporan/ikm/1"}', NOW()),
(2, 'Saran Baru dari Masyarakat', 'Ada saran baru dari responden yang perlu ditindaklanjuti.', 'info', 'inapp', 0, '{"saran_id": 1, "url": "/saran/1"}', NOW()),
(4, 'Peringatan Penurunan IKM', 'Nilai IKM unit Anda mengalami penurunan dibandingkan periode sebelumnya.', 'warning', 'inapp', 1, '{"unit_id": 2, "url": "/dashboard/ikm"}', NOW()),
(1, 'Backup Database Berhasil', 'Backup database harian telah selesai dilaksanakan.', 'info', 'inapp', 1, '{"backup_id": 1}', NOW());

-- ----------------------------------------------------------------------------
-- 13. DATA QUEUE SETTINGS
-- ----------------------------------------------------------------------------
INSERT INTO `tb_queue_settings` (`queue_name`, `is_paused`, `max_workers`, `timeout`, `retry_delay`) VALUES
('email', 0, 5, 300, 60),
('whatsapp', 0, 3, 300, 60),
('pdf', 0, 2, 600, 120),
('excel', 0, 2, 600, 120),
('laporan', 0, 5, 300, 60),
('ikm_kalkulasi', 0, 3, 300, 60);

-- ----------------------------------------------------------------------------
-- 14. DATA QR CODE
-- ----------------------------------------------------------------------------
INSERT INTO `tb_qr_code` (`id_unit`, `id_periode`, `qr_token`, `qr_data`, `scan_count`, `is_active`, `expires_at`) VALUES
(1, 1, 'QR-DISDUKCAPIL-001-T1-2024', '{"unit_id": 1, "periode_id": 1, "url": "https://ikm.go.id/survey/1/1"}', 125, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
(2, 1, 'QR-DINKES-001-T1-2024', '{"unit_id": 2, "periode_id": 1, "url": "https://ikm.go.id/survey/2/1"}', 87, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
(3, 1, 'QR-DIKNAS-001-T1-2024', '{"unit_id": 3, "periode_id": 1, "url": "https://ikm.go.id/survey/3/1"}', 64, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- ----------------------------------------------------------------------------
-- 15. DATA CONSENT LOG
-- ----------------------------------------------------------------------------
INSERT INTO `tb_consent_log` (`id_responden`, `consent_type`, `consent_given`, `consent_version`, `consent_text`, `ip_address`, `user_agent`) VALUES
(1, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(2, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)'),
(3, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.1.102', 'Mozilla/5.0 (Android 11; Mobile)'),
(4, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(5, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.1.104', 'Mozilla/5.0 (Linux; Android 10)'),
(6, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.2.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(7, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.2.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)'),
(8, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.2.102', 'Mozilla/5.0 (Android 12; Mobile)'),
(9, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.3.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(10, 'survey_participation', 1, '1.0', 'Saya setuju untuk berpartisipasi dalam survei ini dan data saya akan digunakan untuk keperluan peningkatan pelayanan.', '192.168.3.101', 'Mozilla/5.0 (iPad; CPU OS 14_0)');

-- ----------------------------------------------------------------------------
-- 16. DATA BACKUP LOG
-- ----------------------------------------------------------------------------
INSERT INTO `tb_backup_log` (`backup_type`, `file_path`, `file_size`, `checksum_md5`, `checksum_sha256`, `retention_days`, `expires_at`, `status`) VALUES
('full', '/backups/db_ikm_20240101_full.sql.gz', 1048576, 'abc123def456', 'sha256hash123', 30, DATE_ADD(NOW(), INTERVAL 30 DAY), 'success'),
('incremental', '/backups/db_ikm_20240102_incremental.sql.gz', 524288, 'def456ghi789', 'sha256hash456', 30, DATE_ADD(NOW(), INTERVAL 30 DAY), 'success'),
('manual', '/backups/db_ikm_20240103_manual.sql.gz', 2097152, 'ghi789jkl012', 'sha256hash789', 90, DATE_ADD(NOW(), INTERVAL 90 DAY), 'success');

-- ============================================================================
-- INFORMASI SETUP SELESAI
-- ============================================================================

-- Tampilkan ringkasan data yang telah diinsert
SELECT 
    'INSTANSI' AS table_name, COUNT(*) AS total_records FROM tb_instansi
UNION ALL SELECT 'UNIT LAYANAN', COUNT(*) FROM tb_unit_layanan
UNION ALL SELECT 'PERIODE', COUNT(*) FROM tb_periode
UNION ALL SELECT 'PENGGUNA', COUNT(*) FROM tb_pengguna
UNION ALL SELECT 'KUESIONER', COUNT(*) FROM tb_kuesioner
UNION ALL SELECT 'RESPONDEN', COUNT(*) FROM tb_responden
UNION ALL SELECT 'JAWABAN SURVEI', COUNT(*) FROM tb_survei_jawaban
UNION ALL SELECT 'SARAN', COUNT(*) FROM tb_saran
UNION ALL SELECT 'REKAP IKM', COUNT(*) FROM tb_rekap_ikm
UNION ALL SELECT 'BAHASA', COUNT(*) FROM tb_bahasa
UNION ALL SELECT 'TRANSLATIONS', COUNT(*) FROM translations;

-- Tampilkan kredensial login default
SELECT 
    '=====================================' AS info,
    'LOGIN CREDENTIALS' AS title,
    '=====================================' AS separator
UNION ALL SELECT '', 'Username: superadmin', ''
UNION ALL SELECT '', 'Password: Admin@123!', ''
UNION ALL SELECT '', 'Role: Super Administrator', ''
UNION ALL SELECT '', '=====================================', ''
UNION ALL SELECT '', 'CATATAN PENTING:', ''
UNION ALL SELECT '', '1. GANTI PASSWORD DEFAULT SEBELUM PRODUCTION!', ''
UNION ALL SELECT '', '2. Aktifkan MFA untuk semua akun admin', ''
UNION ALL SELECT '', '3. Konfigurasi .env sesuai environment', '';

COMMIT;
