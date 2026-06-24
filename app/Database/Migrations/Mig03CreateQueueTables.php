<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk menambahkan tabel-tabel Queue System
 * yang digunakan oleh QueueService.php
 */
class Mig03CreateQueueTables extends Migration
{
    public function up()
    {
        // 1. tb_failed_jobs - Menyimpan job yang gagal
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'job_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'ID unik dari job',
            ],
            'queue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'payload' => [
                'type' => 'TEXT',
                'comment' => 'JSON payload job',
            ],
            'exception' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Stack trace exception',
            ],
            'failed_at' => [
                'type' => 'DATETIME',
            ],
            'retry_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'max_retries' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
            ],
            'next_retry_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('job_id');
        $this->forge->addKey('queue_name');
        $this->forge->addKey('failed_at');
        $this->forge->addKey('next_retry_at');
        $this->forge->createTable('tb_failed_jobs');

        // 2. tb_queue_settings - Konfigurasi queue per queue name
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'queue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'is_paused' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'max_workers' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 5,
            ],
            'timeout' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 300,
                'comment'    => 'Timeout dalam detik',
            ],
            'retry_delay' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 60,
                'comment'    => 'Delay retry dalam detik',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('queue_name');
        $this->forge->createTable('tb_queue_settings');

        // 3. tb_workers - Menyimpan informasi worker yang aktif
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'worker_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'comment'    => 'Unique ID worker',
            ],
            'queue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'pid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Process ID',
            ],
            'hostname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['idle', 'busy', 'stopped'],
                'default'    => 'idle',
            ],
            'current_job_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'last_heartbeat' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('worker_id');
        $this->forge->addKey('queue_name');
        $this->forge->addKey('status');
        $this->forge->createTable('tb_workers');

        // 4. tb_queue_counters - Counter statistik queue
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'queue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'total_jobs' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'completed_jobs' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'failed_jobs' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'avg_processing_time' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Rata-rata waktu proses dalam detik',
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
        $this->forge->addKey('id', true);
        $this->forge->addKey(['queue_name', 'date'], false, false, '', 'UNIQUE');
        $this->forge->createTable('tb_queue_counters');

        // Insert default settings untuk queue yang ada
        $defaultQueues = ['email', 'whatsapp', 'pdf', 'excel', 'laporan', 'ikm_kalkulasi'];
        foreach ($defaultQueues as $queue) {
            $this->db->table('tb_queue_settings')->insert([
                'queue_name'  => $queue,
                'is_paused'   => 0,
                'max_workers' => 5,
                'timeout'     => 300,
                'retry_delay' => 60,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('tb_queue_counters', true);
        $this->forge->dropTable('tb_workers', true);
        $this->forge->dropTable('tb_queue_settings', true);
        $this->forge->dropTable('tb_failed_jobs', true);
    }
}
