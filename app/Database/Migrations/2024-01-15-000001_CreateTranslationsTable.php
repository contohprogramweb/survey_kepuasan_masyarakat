<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateTranslationsTable extends Migration
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
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'translation' => [
                'type' => 'TEXT',
            ],
            'context' => [
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
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['key', 'locale'], true); // Composite unique key
        $this->forge->addKey('locale');
        $this->forge->addKey('key');
        
        $this->forge->createTable('translations');
        
        // Insert default translations for Indonesian (id)
        $defaultTranslationsId = [
            // General
            ['key' => 'skip_to_main_content', 'locale' => 'id', 'translation' => 'Langsung ke konten utama', 'context' => 'Accessibility'],
            ['key' => 'main_navigation', 'locale' => 'id', 'translation' => 'Navigasi utama', 'context' => 'Accessibility'],
            ['key' => 'toggle_navigation', 'locale' => 'id', 'translation' => 'Buka/tutup navigasi', 'context' => 'Accessibility'],
            ['key' => 'language_selection', 'locale' => 'id', 'translation' => 'Pilihan bahasa', 'context' => 'Accessibility'],
            
            // Page titles
            ['key' => 'page_title', 'locale' => 'id', 'translation' => 'Survei Kepuasan Masyarakat', 'context' => 'SEO'],
            ['key' => 'page_description', 'locale' => 'id', 'translation' => 'Survei Kepuasan Masyarakat untuk meningkatkan kualitas layanan publik', 'context' => 'SEO'],
            ['key' => 'page_keywords', 'locale' => 'id', 'translation' => 'IKM, survei, kepuasan masyarakat, layanan publik', 'context' => 'SEO'],
            
            // Default instansi
            ['key' => 'default_instansi_name', 'locale' => 'id', 'translation' => 'Pemerintah Daerah', 'context' => 'Default'],
            ['key' => 'default_instansi_description', 'locale' => 'id', 'translation' => 'Melayani masyarakat dengan sepenuh hati', 'context' => 'Default'],
            ['key' => 'default_instansi_address', 'locale' => 'id', 'translation' => 'Jl. Pelayanan Publik No. 1', 'context' => 'Default'],
            ['key' => 'default_instansi_phone', 'locale' => 'id', 'translation' => '(021) 123-4567', 'context' => 'Default'],
            ['key' => 'default_instansi_email', 'locale' => 'id', 'translation' => 'info@pemda.go.id', 'context' => 'Default'],
            ['key' => 'default_instansi_website', 'locale' => 'id', 'translation' => 'https://pemda.go.id', 'context' => 'Default'],
            
            // Navigation
            ['key' => 'dashboard', 'locale' => 'id', 'translation' => 'Dashboard', 'context' => 'Navigation'],
            ['key' => 'home', 'locale' => 'id', 'translation' => 'Beranda', 'context' => 'Navigation'],
            ['key' => 'surveys', 'locale' => 'id', 'translation' => 'Survei', 'context' => 'Navigation'],
            
            // Hero section
            ['key' => 'welcome_message', 'locale' => 'id', 'translation' => 'Kami menghargai pendapat Anda untuk meningkatkan kualitas layanan kami', 'context' => 'Hero'],
            
            // Unit layanan
            ['key' => 'available_services', 'locale' => 'id', 'translation' => 'Unit Layanan Tersedia', 'context' => 'Services'],
            ['key' => 'no_active_services', 'locale' => 'id', 'translation' => 'Belum ada unit layanan yang aktif saat ini.', 'context' => 'Services'],
            ['key' => 'fill_survey', 'locale' => 'id', 'translation' => 'Isi Survei', 'context' => 'Action'],
            ['key' => 'fill_survey_for', 'locale' => 'id', 'translation' => 'Isi survei untuk', 'context' => 'Action'],
            
            // Dashboard
            ['key' => 'transparency_dashboard', 'locale' => 'id', 'translation' => 'Dashboard Transparansi IKM', 'context' => 'Dashboard'],
            ['key' => 'view_ikm_results', 'locale' => 'id', 'translation' => 'Lihat hasil Indeks Kepuasan Masyarakat secara transparan', 'context' => 'Dashboard'],
            ['key' => 'go_to_dashboard', 'locale' => 'id', 'translation' => 'Ke Dashboard', 'context' => 'Action'],
            ['key' => 'dashboard_title', 'locale' => 'id', 'translation' => 'Dashboard Transparansi IKM', 'context' => 'Dashboard'],
            ['key' => 'dashboard_subtitle', 'locale' => 'id', 'translation' => 'Hasil survei kepuasan masyarakat yang transparan dan akuntabel', 'context' => 'Dashboard'],
            ['key' => 'dashboard_description', 'locale' => 'id', 'translation' => 'Lihat hasil Indeks Kepuasan Masyarakat secara transparan', 'context' => 'SEO'],
            ['key' => 'total_respondents', 'locale' => 'id', 'translation' => 'Total Responden', 'context' => 'Stats'],
            ['key' => 'average_score', 'locale' => 'id', 'translation' => 'Nilai Rata-rata', 'context' => 'Stats'],
            ['key' => 'satisfaction_rate', 'locale' => 'id', 'translation' => 'Tingkat Kepuasan', 'context' => 'Stats'],
            ['key' => 'back_to_home', 'locale' => 'id', 'translation' => 'Kembali ke Beranda', 'context' => 'Action'],
            
            // Footer
            ['key' => 'quick_links', 'locale' => 'id', 'translation' => 'Tautan Cepat', 'context' => 'Footer'],
            ['key' => 'accessibility', 'locale' => 'id', 'translation' => 'Aksesibilitas', 'context' => 'Footer'],
            ['key' => 'skip_to_content', 'locale' => 'id', 'translation' => 'Langsung ke konten', 'context' => 'Footer'],
            ['key' => 'accessibility_statement', 'locale' => 'id', 'translation' => 'Pernyataan Aksesibilitas', 'context' => 'Footer'],
            ['key' => 'privacy_policy', 'locale' => 'id', 'translation' => 'Kebijakan Privasi', 'context' => 'Footer'],
            ['key' => 'all_rights_reserved', 'locale' => 'id', 'translation' => 'Hak Cipta Dilindungi', 'context' => 'Footer'],
        ];
        
        foreach ($defaultTranslationsId as $translation) {
            $this->db->table('translations')->insert($translation);
        }
        
        // Insert default translations for English (en)
        $defaultTranslationsEn = [
            // General
            ['key' => 'skip_to_main_content', 'locale' => 'en', 'translation' => 'Skip to main content', 'context' => 'Accessibility'],
            ['key' => 'main_navigation', 'locale' => 'en', 'translation' => 'Main navigation', 'context' => 'Accessibility'],
            ['key' => 'toggle_navigation', 'locale' => 'en', 'translation' => 'Toggle navigation', 'context' => 'Accessibility'],
            ['key' => 'language_selection', 'locale' => 'en', 'translation' => 'Language selection', 'context' => 'Accessibility'],
            
            // Page titles
            ['key' => 'page_title', 'locale' => 'en', 'translation' => 'Public Satisfaction Survey', 'context' => 'SEO'],
            ['key' => 'page_description', 'locale' => 'en', 'translation' => 'Public Satisfaction Survey to improve public service quality', 'context' => 'SEO'],
            ['key' => 'page_keywords', 'locale' => 'en', 'translation' => 'IKM, survey, public satisfaction, public service', 'context' => 'SEO'],
            
            // Default instansi
            ['key' => 'default_instansi_name', 'locale' => 'en', 'translation' => 'Regional Government', 'context' => 'Default'],
            ['key' => 'default_instansi_description', 'locale' => 'en', 'translation' => 'Serving the community with all our heart', 'context' => 'Default'],
            ['key' => 'default_instansi_address', 'locale' => 'en', 'translation' => 'Public Service Street No. 1', 'context' => 'Default'],
            ['key' => 'default_instansi_phone', 'locale' => 'en', 'translation' => '(021) 123-4567', 'context' => 'Default'],
            ['key' => 'default_instansi_email', 'locale' => 'en', 'translation' => 'info@pemda.go.id', 'context' => 'Default'],
            ['key' => 'default_instansi_website', 'locale' => 'en', 'translation' => 'https://pemda.go.id', 'context' => 'Default'],
            
            // Navigation
            ['key' => 'dashboard', 'locale' => 'en', 'translation' => 'Dashboard', 'context' => 'Navigation'],
            ['key' => 'home', 'locale' => 'en', 'translation' => 'Home', 'context' => 'Navigation'],
            ['key' => 'surveys', 'locale' => 'en', 'translation' => 'Surveys', 'context' => 'Navigation'],
            
            // Hero section
            ['key' => 'welcome_message', 'locale' => 'en', 'translation' => 'We value your feedback to improve our service quality', 'context' => 'Hero'],
            
            // Unit layanan
            ['key' => 'available_services', 'locale' => 'en', 'translation' => 'Available Services', 'context' => 'Services'],
            ['key' => 'no_active_services', 'locale' => 'en', 'translation' => 'No active service units at this time.', 'context' => 'Services'],
            ['key' => 'fill_survey', 'locale' => 'en', 'translation' => 'Fill Survey', 'context' => 'Action'],
            ['key' => 'fill_survey_for', 'locale' => 'en', 'translation' => 'Fill survey for', 'context' => 'Action'],
            
            // Dashboard
            ['key' => 'transparency_dashboard', 'locale' => 'en', 'translation' => 'IKM Transparency Dashboard', 'context' => 'Dashboard'],
            ['key' => 'view_ikm_results', 'locale' => 'en', 'translation' => 'View Public Satisfaction Index results transparently', 'context' => 'Dashboard'],
            ['key' => 'go_to_dashboard', 'locale' => 'en', 'translation' => 'Go to Dashboard', 'context' => 'Action'],
            ['key' => 'dashboard_title', 'locale' => 'en', 'translation' => 'IKM Transparency Dashboard', 'context' => 'Dashboard'],
            ['key' => 'dashboard_subtitle', 'locale' => 'en', 'translation' => 'Transparent and accountable public satisfaction survey results', 'context' => 'Dashboard'],
            ['key' => 'dashboard_description', 'locale' => 'en', 'translation' => 'View Public Satisfaction Index results transparently', 'context' => 'SEO'],
            ['key' => 'total_respondents', 'locale' => 'en', 'translation' => 'Total Respondents', 'context' => 'Stats'],
            ['key' => 'average_score', 'locale' => 'en', 'translation' => 'Average Score', 'context' => 'Stats'],
            ['key' => 'satisfaction_rate', 'locale' => 'en', 'translation' => 'Satisfaction Rate', 'context' => 'Stats'],
            ['key' => 'back_to_home', 'locale' => 'en', 'translation' => 'Back to Home', 'context' => 'Action'],
            
            // Footer
            ['key' => 'quick_links', 'locale' => 'en', 'translation' => 'Quick Links', 'context' => 'Footer'],
            ['key' => 'accessibility', 'locale' => 'en', 'translation' => 'Accessibility', 'context' => 'Footer'],
            ['key' => 'skip_to_content', 'locale' => 'en', 'translation' => 'Skip to content', 'context' => 'Footer'],
            ['key' => 'accessibility_statement', 'locale' => 'en', 'translation' => 'Accessibility Statement', 'context' => 'Footer'],
            ['key' => 'privacy_policy', 'locale' => 'en', 'translation' => 'Privacy Policy', 'context' => 'Footer'],
            ['key' => 'all_rights_reserved', 'locale' => 'en', 'translation' => 'All Rights Reserved', 'context' => 'Footer'],
        ];
        
        foreach ($defaultTranslationsEn as $translation) {
            $this->db->table('translations')->insert($translation);
        }
    }

    public function down()
    {
        $this->forge->dropTable('translations');
    }
}
