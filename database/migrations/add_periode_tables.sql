-- Tabel periode_survei
CREATE TABLE IF NOT EXISTS periode_survei (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    nama_periode VARCHAR(150) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    status ENUM('draft', 'aktif', 'selesai') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (unit_id) REFERENCES unit_layanan(id) ON DELETE CASCADE,
    INDEX idx_unit (unit_id),
    INDEX idx_status (status),
    INDEX idx_dates (tanggal_mulai, tanggal_selesai),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel survei (simulasi relasi agar tidak bisa dihapus jika ada data)
CREATE TABLE IF NOT EXISTS survei (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode_id INT NOT NULL,
    responden_nama VARCHAR(100),
    jawaban TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (periode_id) REFERENCES periode_survei(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dummy
INSERT INTO periode_survei (unit_id, nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES 
(1, 'Survei Kepuasan Q1 2024', '2024-01-01', '2024-03-31', 'selesai'),
(1, 'Survei Kepuasan Q2 2024', '2024-04-01', '2024-06-30', 'aktif'),
(2, 'Evaluasi Keuangan 2024', '2024-02-01', '2024-05-31', 'draft');
