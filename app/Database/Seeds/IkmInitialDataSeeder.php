<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IkmInitialDataSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // ==========================================
        // 1. Unit Layanan Demo
        // ==========================================
        $units = [
            [
                'nama_unit' => 'Dinas Kependudukan dan Pencatatan Sipil',
                'kode_unit' => 'DISDUKCAPIL-001',
                'alamat' => 'Jl. Pelayanan Publik No. 1, Jakarta Pusat',
                'kepala_unit' => 'Dr. Ahmad Wijaya, M.Si',
                'email' => 'info@disdukcapil.go.id',
                'telepon' => '021-12345678',
                'is_active' => 1,
            ],
            [
                'nama_unit' => 'Dinas Kesehatan',
                'kode_unit' => 'DINKES-001',
                'alamat' => 'Jl. Sehat Sentosa No. 45, Jakarta Selatan',
                'kepala_unit' => 'dr. Siti Nurhaliza, M.Kes',
                'email' => 'info@dinkes.go.id',
                'telepon' => '021-87654321',
                'is_active' => 1,
            ],
        ];

        foreach ($units as $unit) {
            $db->table('tb_unit_layanan')->insert($unit);
        }

        // ==========================================
        // 2. Pengguna (Admin Default)
        // ==========================================
        $passwordHash = password_hash('Admin@123!', PASSWORD_DEFAULT);
        
        $users = [
            [
                'id_unit' => 1,
                'username' => 'superadmin',
                'password_hash' => $passwordHash,
                'nama_lengkap' => 'Administrator Sistem',
                'email' => 'superadmin@ikm.go.id',
                'role' => 'super_admin',
                'mfa_enabled' => 0,
                'is_active' => 1,
            ],
            [
                'id_unit' => 1,
                'username' => 'admin_disdukcapil',
                'password_hash' => $passwordHash,
                'nama_lengkap' => 'Admin Disdukcapil',
                'email' => 'admin@disdukcapil.go.id',
                'role' => 'admin_unit',
                'mfa_enabled' => 0,
                'is_active' => 1,
            ],
            [
                'id_unit' => 2,
                'username' => 'admin_dinkes',
                'password_hash' => $passwordHash,
                'nama_lengkap' => 'Admin Dinkes',
                'email' => 'admin@dinkes.go.id',
                'role' => 'admin_unit',
                'mfa_enabled' => 0,
                'is_active' => 1,
            ],
        ];

        foreach ($users as $user) {
            $db->table('tb_pengguna')->insert($user);
        }

        // ==========================================
        // 3. Periode Aktif
        // ==========================================
        $periode = [
            'nama_periode' => 'Triwulan I Tahun 2024',
            'tahun' => 2024,
            'bulan_mulai' => 1,
            'bulan_selesai' => 3,
            'is_active' => 1,
            'is_locked' => 0,
        ];
        $db->table('tb_periode')->insert($periode);

        // ==========================================
        // 4. 9 Unsur Wajib IKM (PermenPANRB 14/2017)
        // ==========================================
        $unsurIkms = [
            [
                'unsur_code' => 'U1',
                'nama_unsur' => 'Persyaratan Pelayanan',
                'deskripsi' => 'Persyaratan teknis dan administratif sesuai ketentuan yang berlaku',
                'bobot' => 1.00,
                'urutan' => 1,
            ],
            [
                'unsur_code' => 'U2',
                'nama_unsur' => 'Prosedur Pelayanan',
                'deskripsi' => 'Alur/prosedur pelayanan yang jelas dan mudah dipahami',
                'bobot' => 1.00,
                'urutan' => 2,
            ],
            [
                'unsur_code' => 'U3',
                'nama_unsur' => 'Waktu Pelayanan',
                'deskripsi' => 'Ketepatan waktu penyelesaian dokumen/layanan',
                'bobot' => 1.00,
                'urutan' => 3,
            ],
            [
                'unsur_code' => 'U4',
                'nama_unsur' => 'Biaya/Tarif',
                'deskripsi' => 'Besaran biaya/tarif yang transparan dan sesuai ketentuan',
                'bobot' => 1.00,
                'urutan' => 4,
            ],
            [
                'unsur_code' => 'U5',
                'nama_unsur' => 'Produk Spesifikasi Jenis Pelayanan',
                'deskripsi' => 'Hasil pelayanan yang sesuai dengan standar yang ditetapkan',
                'bobot' => 1.00,
                'urutan' => 5,
            ],
            [
                'unsur_code' => 'U6',
                'nama_unsur' => 'Kompetensi Pelaksana',
                'deskripsi' => 'Kemampuan dan keterampilan petugas dalam memberikan pelayanan',
                'bobot' => 1.00,
                'urutan' => 6,
            ],
            [
                'unsur_code' => 'U7',
                'nama_unsur' => 'Perilaku Pelaksana',
                'deskripsi' => 'Sikap dan perilaku petugas dalam memberikan pelayanan',
                'bobot' => 1.00,
                'urutan' => 7,
            ],
            [
                'unsur_code' => 'U8',
                'nama_unsur' => 'Penanganan Pengaduan, Saran dan Masukan',
                'deskripsi' => 'Respon terhadap pengaduan, saran dan masukan masyarakat',
                'bobot' => 1.00,
                'urutan' => 8,
            ],
            [
                'unsur_code' => 'U9',
                'nama_unsur' => 'Sarana dan Prasarana',
                'deskripsi' => 'Ketersediaan dan kondisi sarana prasarana pelayanan',
                'bobot' => 1.00,
                'urutan' => 9,
            ],
        ];

        // Insert untuk setiap unit layanan
        for ($unitId = 1; $unitId <= count($units); $unitId++) {
            foreach ($unsurIkms as $unsur) {
                $unsur['id_unit'] = $unitId;
                $unsur['is_active'] = 1;
                $db->table('tb_kuesioner')->insert($unsur);
            }
        }

        // ==========================================
        // 5. Data Bahasa Dasar
        // ==========================================
        $bahasaData = [
            // Bahasa Indonesia
            ['locale' => 'id', 'module' => 'app', 'key' => 'welcome', 'value' => 'Selamat Datang'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'login', 'value' => 'Masuk'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'logout', 'value' => 'Keluar'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'dashboard', 'value' => 'Dasbor'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'survey', 'value' => 'Survei'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'submit', 'value' => 'Kirim'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'cancel', 'value' => 'Batal'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'save', 'value' => 'Simpan'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'delete', 'value' => 'Hapus'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'edit', 'value' => 'Ubah'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'view', 'value' => 'Lihat'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'search', 'value' => 'Cari'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'filter', 'value' => 'Filter'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'export', 'value' => 'Ekspor'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'import', 'value' => 'Impor'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'settings', 'value' => 'Pengaturan'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'profile', 'value' => 'Profil'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'help', 'value' => 'Bantuan'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'yes', 'value' => 'Ya'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'no', 'value' => 'Tidak'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'success', 'value' => 'Berhasil'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'error', 'value' => 'Gagal'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'warning', 'value' => 'Peringatan'],
            ['locale' => 'id', 'module' => 'app', 'key' => 'info', 'value' => 'Informasi'],
            
            // English
            ['locale' => 'en', 'module' => 'app', 'key' => 'welcome', 'value' => 'Welcome'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'login', 'value' => 'Login'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'logout', 'value' => 'Logout'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'dashboard', 'value' => 'Dashboard'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'survey', 'value' => 'Survey'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'submit', 'value' => 'Submit'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'cancel', 'value' => 'Cancel'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'save', 'value' => 'Save'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'delete', 'value' => 'Delete'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'edit', 'value' => 'Edit'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'view', 'value' => 'View'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'search', 'value' => 'Search'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'filter', 'value' => 'Filter'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'export', 'value' => 'Export'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'import', 'value' => 'Import'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'settings', 'value' => 'Settings'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'profile', 'value' => 'Profile'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'help', 'value' => 'Help'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'yes', 'value' => 'Yes'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'no', 'value' => 'No'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'success', 'value' => 'Success'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'error', 'value' => 'Error'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'warning', 'value' => 'Warning'],
            ['locale' => 'en', 'module' => 'app', 'key' => 'info', 'value' => 'Information'],
        ];

        foreach ($bahasaData as $bahasa) {
            $bahasa['is_active'] = 1;
            $db->table('tb_bahasa')->insert($bahasa);
        }

        // ==========================================
        // Informasi Seed untuk Developer
        // ==========================================
        echo "\n";
        echo "===========================================\n";
        echo "   SEEDING BERHASIL - APLIKASI IKM v2.0.0\n";
        echo "===========================================\n";
        echo "\n";
        echo "DATA YANG DIBUAT:\n";
        echo "-----------------\n";
        echo "✓ 2 Unit Layanan (Disdukcapil & Dinkes)\n";
        echo "✓ 3 Pengguna:\n";
        echo "  - superadmin / Admin@123! (Super Admin)\n";
        echo "  - admin_disdukcapil / Admin@123! (Admin Unit 1)\n";
        echo "  - admin_dinkes / Admin@123! (Admin Unit 2)\n";
        echo "✓ 1 Periode Aktif (Triwulan I 2024)\n";
        echo "✓ 18 Kuesioner (9 unsur x 2 unit)\n";
        echo "✓ 48 Entri Bahasa (ID & EN)\n";
        echo "\n";
        echo "AKSES APLIKASI:\n";
        echo "---------------\n";
        echo "URL: http://localhost:8080\n";
        echo "Username: superadmin\n";
        echo "Password: Admin@123!\n";
        echo "\n";
        echo "CATATAN PENTING:\n";
        echo "----------------\n";
        echo "⚠ GANTI PASSWORD DEFAULT SEBELUM PRODUCTION!\n";
        echo "⚠ Aktifkan MFA untuk semua akun admin\n";
        echo "⚠ Konfigurasi OAuth2/OIDC sesuai kebutuhan\n";
        echo "\n";
    }
}
