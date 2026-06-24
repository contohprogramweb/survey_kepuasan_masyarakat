<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateIkmTables extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. tb_unit_layanan (Master Data Unit Layanan)
        $this->forge->addField([
            'id_unit' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'kode_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'alamat' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'kepala_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'logo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_unit', true);
        $this->forge->addKey('kode_unit');
        $this->forge->addKey('is_active');
        $this->forge->createTable('tb_unit_layanan');

        // 2. tb_pengguna (Users)
        $this->forge->addField([
            'id_pengguna' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['super_admin', 'admin_unit', 'operator', 'viewer'],
                'default'    => 'operator',
            ],
            'mfa_secret' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'mfa_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'oauth_provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'oauth_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_pengguna', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addKey('username');
        $this->forge->addKey('email');
        $this->forge->addKey('role');
        $this->forge->createTable('tb_pengguna');

        // 3. tb_periode
        $this->forge->addField([
            'id_periode' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_periode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'bulan_mulai' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'bulan_selesai' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_periode', true);
        $this->forge->addKey(['tahun', 'bulan_mulai', 'bulan_selesai']);
        $this->forge->addKey('is_active');
        $this->forge->createTable('tb_periode');

        // 4. tb_kuesioner (9 Unsur Wajib IKM)
        $this->forge->addField([
            'id_kuesioner' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'unsur_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'comment'    => 'U1-U9',
            ],
            'nama_unsur' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'bobot' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_kuesioner', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addKey(['id_unit', 'unsur_code'], false, false, '', 'UNIQUE');
        $this->forge->addKey('is_active');
        $this->forge->createTable('tb_kuesioner');

        // 5. tb_responden (UU PDP Compliance)
        $this->forge->addField([
            'id_responden' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_periode' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
                'comment'    => 'Encrypted',
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Encrypted',
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => true,
            ],
            'usia' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
            ],
            'pendidikan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'pekerjaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Encrypted',
            ],
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Encrypted',
            ],
            'consent_given' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'consent_timestamp' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'consent_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'data_retention_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_responden', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_periode', 'tb_periode', 'id_periode', 'RESTRICT', 'CASCADE');
        $this->forge->addKey(['id_unit', 'id_periode']);
        $this->forge->addKey('consent_given');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tb_responden');

        // 6. tb_survei_jawaban (PARTITIONED BY id_periode)
        $this->forge->addField([
            'id_jawaban' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_responden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kuesioner' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_periode' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nilai' => [
                'type'       => 'INT',
                'constraint' => 1,
                'comment'    => '1-4 (Skala Likert)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_jawaban', true);
        $this->forge->addForeignKey('id_responden', 'tb_responden', 'id_responden', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_kuesioner', 'tb_kuesioner', 'id_kuesioner', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_periode', 'tb_periode', 'id_periode', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addKey(['id_periode', 'id_unit']);
        $this->forge->addKey('created_at');

        // Buat tabel dengan partisi manual karena Forge tidak support partitioning syntax langsung
        $sql = $this->db->prefixTable('tb_survei_jawaban');
        $createTableSQL = "CREATE TABLE {$sql} (
            `id_jawaban` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_responden` INT UNSIGNED NOT NULL,
            `id_kuesioner` INT UNSIGNED NOT NULL,
            `id_periode` INT UNSIGNED NOT NULL,
            `id_unit` INT UNSIGNED NOT NULL,
            `nilai` INT(1) NOT NULL COMMENT '1-4 (Skala Likert)',
            `created_at` DATETIME NULL,
            PRIMARY KEY (`id_jawaban`, `id_periode`),
            KEY `idx_responden` (`id_responden`),
            KEY `idx_kuesioner` (`id_kuesioner`),
            KEY `idx_periode_unit` (`id_periode`, `id_unit`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_jawaban_responden` FOREIGN KEY (`id_responden`) REFERENCES {$this->db->prefixTable('tb_responden')} (`id_responden`) ON DELETE RESTRICT,
            CONSTRAINT `fk_jawaban_kuesioner` FOREIGN KEY (`id_kuesioner`) REFERENCES {$this->db->prefixTable('tb_kuesioner')} (`id_kuesioner`) ON DELETE RESTRICT,
            CONSTRAINT `fk_jawaban_periode` FOREIGN KEY (`id_periode`) REFERENCES {$this->db->prefixTable('tb_periode')} (`id_periode`) ON DELETE RESTRICT,
            CONSTRAINT `fk_jawaban_unit` FOREIGN KEY (`id_unit`) REFERENCES {$this->db->prefixTable('tb_unit_layanan')} (`id_unit`) ON DELETE RESTRICT
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
        )";

        $this->db->query($createTableSQL);

        // 7. tb_saran
        $this->forge->addField([
            'id_saran' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_responden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'pesan' => [
                'type'       => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['baru', 'diproses', 'ditindaklanjuti', 'ditolak'],
                'default'    => 'baru',
            ],
            'tanggapan' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'ditanggapi_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ditanggapi_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_saran', true);
        $this->forge->addForeignKey('id_responden', 'tb_responden', 'id_responden', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('ditanggapi_oleh', 'tb_pengguna', 'id_pengguna', 'SET NULL', 'SET NULL');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tb_saran');

        // 8. tb_rekap_ikm
        $this->forge->addField([
            'id_rekap' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_periode' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jumlah_responden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'nilai_ikm_mentah' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'nilai_ikm_final' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'mutu_layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'A/B/C/D',
            ],
            'delta_ikm' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Perubahan dari periode sebelumnya',
            ],
            'flag_alert' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 jika ada penurunan signifikan',
            ],
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'published_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_rekap', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_periode', 'tb_periode', 'id_periode', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('published_by', 'tb_pengguna', 'id_pengguna', 'SET NULL', 'SET NULL');
        $this->forge->addUniqueKey(['id_unit', 'id_periode']);
        $this->forge->addKey('is_published');
        $this->forge->addKey('flag_alert');
        $this->forge->createTable('tb_rekap_ikm');

        // 9. tb_audit_log (PARTITION BY YEAR)
        $this->forge->addField([
            'id_log' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pengguna' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'table_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'record_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'old_value' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON',
            ],
            'new_value' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_log', true);
        $this->forge->addForeignKey('id_pengguna', 'tb_pengguna', 'id_pengguna', 'SET NULL', 'SET NULL');
        $this->forge->addKey(['action', 'created_at']);
        $this->forge->addKey('table_name');
        $this->forge->addKey('created_at');

        // Buat tabel audit_log dengan partisi tahun
        $sql = $this->db->prefixTable('tb_audit_log');
        $currentYear = date('Y');
        $partitions = '';
        for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
            $partitions .= "PARTITION p{$i} VALUES LESS THAN ('" . ($i + 1) . "-01-01'),\n";
        }
        $partitions .= "PARTITION pmax VALUES LESS THAN MAXVALUE";

        $createAuditSQL = "CREATE TABLE {$sql} (
            `id_log` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_pengguna` INT UNSIGNED NULL,
            `action` VARCHAR(100) NOT NULL,
            `table_name` VARCHAR(100) NULL,
            `record_id` VARCHAR(50) NULL,
            `old_value` TEXT NULL COMMENT 'JSON',
            `new_value` TEXT NULL COMMENT 'JSON',
            `ip_address` VARCHAR(45) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` DATETIME NULL,
            PRIMARY KEY (`id_log`, `created_at`),
            KEY `idx_action_created` (`action`, `created_at`),
            KEY `idx_table_name` (`table_name`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_audit_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES {$this->db->prefixTable('tb_pengguna')} (`id_pengguna`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        PARTITION BY RANGE (YEAR(`created_at`)) (
            {$partitions}
        )";

        $this->db->query($createAuditSQL);

        // 10. tb_notifikasi
        $this->forge->addField([
            'id_notifikasi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pengguna' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'channel' => [
                'type'       => 'ENUM',
                'constraint' => ['email', 'sms', 'whatsapp', 'push', 'in_app'],
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'message' => [
                'type'       => 'TEXT',
            ],
            'data_payload' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON',
            ],
            'delivery_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'sent', 'delivered', 'failed'],
                'default'    => 'pending',
            ],
            'retry_count' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_notifikasi', true);
        $this->forge->addForeignKey('id_pengguna', 'tb_pengguna', 'id_pengguna', 'SET NULL', 'SET NULL');
        $this->forge->addKey(['id_pengguna', 'delivery_status']);
        $this->forge->addKey('delivery_status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tb_notifikasi');

        // 11. tb_backup_log
        $this->forge->addField([
            'id_backup' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'backup_type' => [
                'type'       => 'ENUM',
                'constraint' => ['full', 'incremental', 'database_only', 'file_only'],
            ],
            'storage_target' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'file_size_bytes' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'checksum_sha256' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'encryption_key_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'is_encrypted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'retention_days' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 30,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'failed', 'in_progress'],
                'default'    => 'in_progress',
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_backup', true);
        $this->forge->addKey('backup_type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('tb_backup_log');

        // 12. tb_bahasa
        $this->forge->addField([
            'id_bahasa' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'app',
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'value' => [
                'type'       => 'TEXT',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_bahasa', true);
        $this->forge->addUniqueKey(['locale', 'module', 'key']);
        $this->forge->addKey('locale');
        $this->forge->addKey('module');
        $this->forge->createTable('tb_bahasa');

        // 13. tb_qr_code
        $this->forge->addField([
            'id_qr' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_periode' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'short_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'qr_data' => [
                'type'       => 'TEXT',
            ],
            'format' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'png',
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'scan_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_scanned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_qr', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_periode', 'tb_periode', 'id_periode', 'RESTRICT', 'CASCADE');
        $this->forge->addKey(['id_unit', 'is_active']);
        $this->forge->addKey('short_url');
        $this->forge->createTable('tb_qr_code');

        // 14. tb_consent_log (UU PDP Audit Trail)
        $this->forge->addField([
            'id_consent' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_responden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'consent_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'survey_participation, data_processing, marketing, etc',
            ],
            'consent_given' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
            ],
            'consent_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'consent_text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_fingerprint' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_consent', true);
        $this->forge->addForeignKey('id_responden', 'tb_responden', 'id_responden', 'SET NULL', 'SET NULL');
        $this->forge->addKey(['id_responden', 'consent_type']);
        $this->forge->addKey('consent_type');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tb_consent_log');

        // 15. tb_queue_jobs (Fallback Persistence)
        $this->forge->addField([
            'id_job' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'queue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'job_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'payload' => [
                'type'       => 'TEXT',
                'comment'    => 'JSON',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'failed'],
                'default'    => 'pending',
            ],
            'attempts' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'max_attempts' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 3,
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'failed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'available_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_job', true);
        $this->forge->addKey(['queue_name', 'status']);
        $this->forge->addKey('status');
        $this->forge->addKey('available_at');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tb_queue_jobs');

        // 16. tb_api_keys
        $this->forge->addField([
            'id_api_key' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_unit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'key_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'api_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'api_secret' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON array of allowed endpoints/actions',
            ],
            'rate_limit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 100,
                'comment'    => 'Requests per minute',
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoked_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_api_key', true);
        $this->forge->addForeignKey('id_unit', 'tb_unit_layanan', 'id_unit', 'SET NULL', 'CASCADE');
        $this->forge->addKey('api_key');
        $this->forge->addKey('is_active');
        $this->forge->addKey(['id_unit', 'is_active']);
        $this->forge->createTable('tb_api_keys');

        // ==========================================
        // TRIGGERS untuk created_at dan updated_at
        // ==========================================
        
        $tables_needing_triggers = [
            'tb_unit_layanan',
            'tb_pengguna',
            'tb_periode',
            'tb_kuesioner',
            'tb_responden',
            'tb_saran',
            'tb_rekap_ikm',
            'tb_notifikasi',
            'tb_backup_log',
            'tb_bahasa',
            'tb_qr_code',
            'tb_queue_jobs',
            'tb_api_keys'
        ];

        foreach ($tables_needing_triggers as $table) {
            $tableName = $this->db->prefixTable($table);
            
            // Trigger INSERT
            $triggerInsert = "
                CREATE TRIGGER trg_{$table}_before_insert
                BEFORE INSERT ON {$tableName}
                FOR EACH ROW
                BEGIN
                    IF NEW.created_at IS NULL THEN
                        SET NEW.created_at = NOW();
                    END IF;
                    IF NEW.updated_at IS NULL THEN
                        SET NEW.updated_at = NOW();
                    END IF;
                END
            ";
            $this->db->query($triggerInsert);

            // Trigger UPDATE
            $triggerUpdate = "
                CREATE TRIGGER trg_{$table}_before_update
                BEFORE UPDATE ON {$tableName}
                FOR EACH ROW
                BEGIN
                    SET NEW.updated_at = NOW();
                END
            ";
            $this->db->query($triggerUpdate);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Drop triggers first
        $tables_needing_triggers = [
            'tb_unit_layanan',
            'tb_pengguna',
            'tb_periode',
            'tb_kuesioner',
            'tb_responden',
            'tb_saran',
            'tb_rekap_ikm',
            'tb_notifikasi',
            'tb_backup_log',
            'tb_bahasa',
            'tb_qr_code',
            'tb_queue_jobs',
            'tb_api_keys'
        ];

        foreach ($tables_needing_triggers as $table) {
            try {
                $this->db->query("DROP TRIGGER IF EXISTS trg_{$table}_before_insert");
                $this->db->query("DROP TRIGGER IF EXISTS trg_{$table}_before_update");
            } catch (\Exception $e) {
                // Ignore trigger drop errors
            }
        }

        // Drop tables in reverse order of dependencies
        $tables = [
            'tb_api_keys',
            'tb_queue_jobs',
            'tb_consent_log',
            'tb_qr_code',
            'tb_bahasa',
            'tb_backup_log',
            'tb_notifikasi',
            'tb_audit_log',
            'tb_rekap_ikm',
            'tb_saran',
            'tb_survei_jawaban',
            'tb_responden',
            'tb_kuesioner',
            'tb_periode',
            'tb_pengguna',
            'tb_unit_layanan'
        ];

        foreach ($tables as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
