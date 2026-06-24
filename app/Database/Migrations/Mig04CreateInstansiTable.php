<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk menambahkan tabel tb_instansi
 * sebagai master data instansi/organisasi
 */
class Mig04CreateInstansiTable extends Migration
{
    public function up()
    {
        // tb_instansi - Master Data Instansi/Organisasi
        $this->forge->addField([
            'id_instansi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_instansi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'kode_instansi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'alamat' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'provinsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'kode_pos' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'fax' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'logo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kepala_instansi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nip_kepala' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'visi' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'misi' => [
                'type'       => 'TEXT',
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
        $this->forge->addKey('id_instansi', true);
        $this->forge->addKey('kode_instansi');
        $this->forge->addKey('is_active');
        $this->forge->createTable('tb_instansi');

        // Tambahkan foreign key ke tb_unit_layanan jika belum ada
        // (asumsi: tb_unit_layanan sudah memiliki kolom id_instansi atau akan ditambahkan)
        // Jika diperlukan, tambahkan kolom id_instansi ke tb_unit_layanan
        // Untuk sekarang, kita biarkan relasi ini opsional
    }

    public function down()
    {
        $this->forge->dropTable('tb_instansi', true);
    }
}
