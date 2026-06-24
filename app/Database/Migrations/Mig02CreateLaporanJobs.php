<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class Mig02CreateLaporanJobs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis_laporan' => [
                'type'       => 'ENUM',
                'constraint' => ['pdf', 'excel'],
                'comment'    => 'Jenis laporan: pdf atau excel',
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Filter berdasarkan unit (NULL = semua unit)',
            ],
            'periode_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Filter berdasarkan periode (NULL = semua periode)',
            ],
            'tanggal_mulai' => [
                'type'       => 'DATE',
                'null'       => true,
                'comment'    => 'Tanggal mulai periode laporan',
            ],
            'tanggal_selesai' => [
                'type'       => 'DATE',
                'null'       => true,
                'comment'    => 'Tanggal selesai periode laporan',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'failed'],
                'default'    => 'pending',
                'comment'    => 'Status job queue',
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Path file hasil generate',
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nama file asli',
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Pesan error jika gagal',
            ],
            'progress' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Progress percentage (0-100)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu penyelesaian job',
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addKey('created_at');
        
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'unit_layanan', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('periode_id', 'periode', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('laporan_jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('laporan_jobs', true);
    }
}
