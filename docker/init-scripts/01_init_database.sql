# =============================================================================
# MySQL Initialization Script untuk IKM v2.0.0
# Script ini dijalankan otomatis saat container MySQL pertama kali dibuat
# =============================================================================

-- Create database dengan character set yang tepat
CREATE DATABASE IF NOT EXISTS ikm_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Grant privileges
GRANT ALL PRIVILEGES ON ikm_db.* TO 'ikm_user'@'%';
FLUSH PRIVILEGES;

-- Use database
USE ikm_db;

-- =============================================================================
-- Contoh tabel dengan partitioning (akan dikembangkan di migration nanti)
-- =============================================================================

-- Tabel survei dengan partitioning berdasarkan tahun
CREATE TABLE IF NOT EXISTS surveys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('draft', 'active', 'closed', 'archived') DEFAULT 'draft',
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);

-- Tabel responses dengan partitioning berdasarkan bulan
CREATE TABLE IF NOT EXISTS survey_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id BIGINT UNSIGNED NOT NULL,
    respondent_id VARCHAR(100),
    submission_data JSON,
    total_score DECIMAL(5,2),
    status ENUM('submitted', 'validated', 'rejected') DEFAULT 'submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    validated_at TIMESTAMP NULL,
    validated_by BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_survey_id (survey_id),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_status (status)
) ENGINE=InnoDB
PARTITION BY RANGE (TO_DAYS(submitted_at)) (
    PARTITION p2024_q1 VALUES LESS THAN (TO_DAYS('2024-04-01')),
    PARTITION p2024_q2 VALUES LESS THAN (TO_DAYS('2024-07-01')),
    PARTITION p2024_q3 VALUES LESS THAN (TO_DAYS('2024-10-01')),
    PARTITION p2024_q4 VALUES LESS THAN (TO_DAYS('2025-01-01')),
    PARTITION p2025_q1 VALUES LESS THAN (TO_DAYS('2025-04-01')),
    PARTITION p2025_q2 VALUES LESS THAN (TO_DAYS('2025-07-01')),
    PARTITION p2025_q3 VALUES LESS THAN (TO_DAYS('2025-10-01')),
    PARTITION p2025_q4 VALUES LESS THAN (TO_DAYS('2026-01-01')),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);

-- Tabel audit log untuk compliance UU PDP
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id BIGINT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB
PARTITION BY RANGE (TO_DAYS(created_at)) (
    PARTITION p2024_h1 VALUES LESS THAN (TO_DAYS('2024-07-01')),
    PARTITION p2024_h2 VALUES LESS THAN (TO_DAYS('2025-01-01')),
    PARTITION p2025_h1 VALUES LESS THAN (TO_DAYS('2025-07-01')),
    PARTITION p2025_h2 VALUES LESS THAN (TO_DAYS('2026-01-01')),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);

-- Tabel consent management untuk UU PDP compliance
CREATE TABLE IF NOT EXISTS consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    respondent_email VARCHAR(255),
    consent_type ENUM('data_processing', 'marketing', 'analytics', 'third_party') NOT NULL,
    status ENUM('granted', 'denied', 'withdrawn') NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    withdrawn_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_status (status),
    INDEX idx_granted_at (granted_at)
) ENGINE=InnoDB;

-- Tabel queue jobs untuk background processing
CREATE TABLE IF NOT EXISTS queue_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue_name VARCHAR(50) NOT NULL,
    job_class VARCHAR(255) NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_status (queue_name, status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Insert data awal untuk testing
INSERT INTO surveys (survey_code, title, description, status, start_date, end_date) VALUES
('IKM-2024-001', 'Survei Kepuasan Masyarakat Q1 2024', 'Survei triwulan pertama untuk mengukur kepuasan masyarakat terhadap layanan publik', 'active', '2024-01-01 00:00:00', '2024-03-31 23:59:59'),
('IKM-2024-002', 'Survei Kepuasan Masyarakat Q2 2024', 'Survei triwulan kedua untuk mengukur kepuasan masyarakat terhadap layanan publik', 'draft', '2024-04-01 00:00:00', '2024-06-30 23:59:59');

-- Create view untuk monitoring
CREATE OR REPLACE VIEW v_survey_statistics AS
SELECT 
    s.id,
    s.survey_code,
    s.title,
    s.status,
    COUNT(DISTINCT r.id) as total_responses,
    SUM(CASE WHEN r.status = 'submitted' THEN 1 ELSE 0 END) as submitted_count,
    SUM(CASE WHEN r.status = 'validated' THEN 1 ELSE 0 END) as validated_count,
    AVG(r.total_score) as average_score,
    MIN(r.submitted_at) as first_response,
    MAX(r.submitted_at) as last_response
FROM surveys s
LEFT JOIN survey_responses r ON s.id = r.survey_id
GROUP BY s.id, s.survey_code, s.title, s.status;

SELECT 'Database initialization completed successfully!' as status;
