-- Tabel unit_layanan
CREATE TABLE IF NOT EXISTS unit_layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_unit VARCHAR(50) NOT NULL UNIQUE,
    nama_unit VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    alamat TEXT,
    kontak VARCHAR(100),
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel periode (untuk simulasi relasi)
CREATE TABLE IF NOT EXISTS periode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    nama_periode VARCHAR(100),
    tahun_awal INT,
    tahun_akhir INT,
    FOREIGN KEY (unit_id) REFERENCES unit_layanan(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dummy
INSERT INTO unit_layanan (kode_unit, nama_unit, deskripsi, status) VALUES 
('U001', 'Unit Teknologi Informasi', 'Mengelola infrastruktur IT', 'aktif'),
('U002', 'Unit Keuangan', 'Mengelola keuangan instansi', 'aktif'),
('U003', 'Unit SDM', 'Mengelola sumber daya manusia', 'nonaktif');
