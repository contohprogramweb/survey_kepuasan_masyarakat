<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Mig01CreateNotifications extends Migration
{
    public function up()
    {
        // Tabel Notifikasi
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
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type'       => 'TEXT',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['info', 'warning', 'danger', 'success'],
                'default'    => 'info',
            ],
            'channel' => [
                'type'       => 'ENUM',
                'constraint' => ['inapp', 'email', 'whatsapp'],
                'default'    => 'inapp',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'data' => [
                'type'       => 'JSON',
                'null'       => true,
                'comment'    => 'Additional data (e.g., survey_id, url)',
            ],
            'sent_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_read');
        $this->forge->createTable('notifications');

        // Tabel Preferensi Notifikasi
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
                'unique'     => true,
            ],
            'enable_inapp' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'enable_email' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'enable_whatsapp' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('notification_preferences');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
        $this->forge->dropTable('notification_preferences');
    }
}
