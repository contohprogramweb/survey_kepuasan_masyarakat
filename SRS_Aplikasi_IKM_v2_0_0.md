**SOFTWARE REQUIREMENTS SPECIFICATION**

**APLIKASI SURVEI KEPUASAN MASYARAKAT**

**(INDEKS KEPUASAN MASYARAKAT / IKM)**

_Instansi Pemerintah_

| **Versi**            | 2.0.0               |
| -------------------- | ------------------- |
| **Tanggal**          | 2025                |
| **Status**           | Draft Revisi        |
| **Diklasifikasikan** | Internal / Terbatas |

# **DAFTAR ISI**

# **1\. PENDAHULUAN**

## **1.1 Tujuan Dokumen**

Dokumen Software Requirements Specification (SRS) versi 2.0.0 ini merupakan revisi mayor dari versi 1.1.0, disusun untuk mendefinisikan secara lengkap dan terstruktur semua kebutuhan fungsional dan non-fungsional dari Aplikasi Survei Kepuasan Masyarakat / Indeks Kepuasan Masyarakat (IKM) yang akan dikembangkan untuk instansi pemerintah.

Revisi versi 2.0.0 ini melakukan upgrade menyeluruh dari CodeIgniter 3 ke CodeIgniter 4.4+, PHP 8.2+, serta menambahkan kapabilitas enterprise berikut:

- Queue system berbasis Redis untuk pemrosesan asinkron
- Partitioning database untuk skalabilitas data besar
- Backup robust dengan Percona XtraBackup dan S3 offsite
- QR Code modern dengan Endroid QR Code 5.x
- Notifikasi multi-channel: Email + WhatsApp Business API
- Autentikasi modern: OAuth 2.0, OpenID Connect, SAML 2.0
- REST API publik dengan OpenAPI 3.0
- UU PDP full compliance dengan consent management
- DevOps: Docker, CI/CD, Kubernetes-ready
- Monitoring: Prometheus, Grafana, structured logging

## **1.2 Lingkup Sistem**

Aplikasi IKM ini merupakan sistem informasi berbasis web yang dibangun untuk memfasilitasi pengukuran, pengolahan, dan pelaporan Indeks Kepuasan Masyarakat sesuai dengan PermenPANRB Nomor 14 Tahun 2017. Pada versi 2.0.0, lingkup sistem diperluas dengan arsitektur modern (microservices-ready monolith):

- Homepage / Landing Page publik sebagai pintu masuk responden
- Manajemen konfigurasi survei (periode, unit layanan, responden)
- Pengisian kuesioner survei oleh masyarakat melalui antarmuka web responsif dengan consent management (UU PDP)
- Pengolahan dan kalkulasi otomatis IKM berbasis queue
- Pelaporan dan visualisasi hasil survei dengan dashboard publik SEO
- REST API publik untuk third-party integration
- QR Code modern dengan UTM tracking dan analytics
- Manajemen saran, pengaduan, dan tindak lanjut
- Sistem notifikasi multi-channel (Email + WhatsApp)
- Backup dan restore database (hot backup + S3 offsite)
- Dukungan multi-bahasa berbasis database
- CI/CD pipeline dengan Docker dan Kubernetes

## **1.3 Definisi, Akronim, dan Singkatan**

| **Istilah / Akronim** | **Definisi**                                                                                                      |
| --------------------- | ----------------------------------------------------------------------------------------------------------------- |
| IKM                   | Indeks Kepuasan Masyarakat - nilai indeks yang mencerminkan tingkat kepuasan masyarakat terhadap pelayanan publik |
| SKM                   | Survei Kepuasan Masyarakat - kegiatan pengukuran secara komprehensif tentang tingkat kepuasan masyarakat          |
| SRS                   | Software Requirements Specification - dokumen spesifikasi kebutuhan perangkat lunak                               |
| PermenPANRB           | Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi                                           |
| CI4                   | CodeIgniter 4 - PHP framework MVC modern dengan namespace, middleware, service container                          |
| RBAC                  | Role-Based Access Control - mekanisme pengendalian akses berbasis peran                                           |
| SSO                   | Single Sign-On - sistem autentikasi terpusat                                                                      |
| LDAP                  | Lightweight Directory Access Protocol - protokol autentikasi direktori                                            |
| API                   | Application Programming Interface - antarmuka pemrograman antar layanan                                           |
| DomPDF                | Library PHP untuk menghasilkan dokumen PDF dari HTML/CSS                                                          |
| QR Code               | Quick Response Code - kode matriks 2D untuk akses cepat URL survei                                                |
| NRR                   | Nilai Rata-Rata - rata-rata nilai dari tiap unsur penilaian                                                       |
| NRR Tertimbang        | Nilai Rata-Rata Tertimbang - NRR dikalikan dengan bobot (0,111 untuk 9 unsur)                                     |
| Landing Page          | Halaman pertama yang dilihat responden sebelum mengisi survei                                                     |
| JWT                   | JSON Web Token - standar token autentikasi berbasis JSON                                                          |
| OAuth 2.0             | Protokol otorisasi modern untuk SSO dan third-party access                                                        |
| SAML 2.0              | Security Assertion Markup Language - protokol SSO berbasis XML                                                    |
| DPO                   | Data Protection Officer - petugas perlindungan data sesuai UU PDP                                                 |
| UU PDP                | Undang-Undang Perlindungan Data Pribadi                                                                           |
| Redis                 | In-memory data store untuk cache, queue, dan session                                                              |
| CDN                   | Content Delivery Network - jaringan distribusi konten statis                                                      |
| WORM                  | Write Once Read Many - penyimpanan data yang tidak dapat dimodifikasi                                             |
| RPO                   | Recovery Point Objective - target maksimum data loss yang dapat diterima                                          |
| RTO                   | Recovery Time Objective - target waktu pemulihan sistem                                                           |
| DataTables            | Plugin jQuery untuk menampilkan data tabular dengan server-side processing                                        |
| SweetAlert            | Library JavaScript untuk menampilkan dialog/notifikasi interaktif                                                 |

## **1.4 Referensi dan Dasar Hukum**

- Undang-Undang Nomor 25 Tahun 2009 tentang Pelayanan Publik
- Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik
- Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP) \[BARU\]
- Peraturan Pemerintah Nomor 96 Tahun 2012 tentang Pelaksanaan UU No. 25 Tahun 2009
- PermenPANRB Nomor 14 Tahun 2017 tentang Pedoman Penyusunan Survei Kepuasan Masyarakat
- PermenPANRB Nomor 17 Tahun 2017 tentang Pedoman Penilaian Kinerja Unit Penyelenggara Pelayanan Publik
- IEEE Std 830-1998 - IEEE Recommended Practice for Software Requirements Specifications
- ISO/IEC 25010:2011 - Systems and Software Quality Requirements and Evaluation (SQuaRE)
- OWASP ASVS 4.0 - Application Security Verification Standard \[BARU\]

## **1.5 Riwayat Revisi Dokumen**

| **Versi** | **Tanggal** | **Deskripsi Perubahan**                                                                                                                                                                                                    | **Penulis**    |
| --------- | ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------- |
| 1.0.0     | 2025        | Pembuatan dokumen awal SRS Aplikasi IKM                                                                                                                                                                                    | Tim Pengembang |
| 1.1.0     | 2025        | Penambahan: Homepage/Landing Page, QR Code Generator, Notifikasi & Reminder, Manajemen Saran/Pengaduan, Analisis Gap IKM, Multi-Bahasa, Preview Kuesioner, Dashboard Publik, Backup/Restore, Integrasi SSO/LDAP (opsional) | Tim Pengembang |
| 2.0.0     | 2025        | Upgrade mayor: CI3 -> CI4.4+, PHP 8.2+, Redis Queue, Partitioning DB, Percona XtraBackup, Endroid QR Code 5.x, WhatsApp Business API, OAuth2/SAML2/OIDC, REST API, UU PDP Compliance, Docker/CI-CD/K8s, Prometheus/Grafana | Tim Pengembang |

# **2\. DESKRIPSI UMUM SISTEM**

## **2.1 Perspektif Produk**

Aplikasi IKM merupakan sistem mandiri (standalone web application) berbasis PHP dengan framework CodeIgniter 4.4+. Pada versi 2.0.0, sistem dibangun dengan arsitektur Microservices-Ready Monolith yang mendukung horizontal scaling, queue-based processing, dan observability penuh.

| **Lapisan**   | **Teknologi**                                                                                                                            | **Fungsi**                                                                      |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| Presentasi    | Bootstrap 5.3, jQuery 3.7 (admin), Vanilla JS ES6+ (publik), DataTables, SweetAlert2, Chart.js 4, Vite                                   | Antarmuka responsif, validasi klien, grafik IKM, landing page publik            |
| Logika Bisnis | CodeIgniter 4.4.x, PHP 8.2+, DomPDF 3.0, PhpSpreadsheet 2.x, Endroid QR Code 5.x, league/oauth2-client, onelogin/php-saml, predis/predis | MVC, kalkulasi IKM queue-based, QR Code, autentikasi modern, generasi PDF/Excel |
| Data          | MySQL 8.0 / MariaDB 10.6 (Partitioned), Redis 7.x, S3-compatible Storage                                                                 | Penyimpanan data survei, cache, session, queue, backup offsite                  |
| Infrastruktur | Docker, Kubernetes, Nginx 1.24+, PHP-FPM, CDN, WAF                                                                                       | Container orchestration, web server, HTTPS, reverse proxy, DDoS protection      |
| Observability | Prometheus + Grafana, ELK/Loki, Jaeger/Zipkin, GitHub Actions/GitLab CI                                                                  | Metrics, logging, tracing, CI/CD pipeline                                       |

## **2.2 Fungsi Utama Sistem**

Versi 2.0.0 menambahkan 4 fungsi baru (F-23 s.d. F-26) di samping 22 fungsi yang sudah ada pada versi 1.1.0:

| **No** | **Fungsi**                         | **Deskripsi Singkat**                                                       | **Status**         |
| ------ | ---------------------------------- | --------------------------------------------------------------------------- | ------------------ |
| F-01   | Manajemen Pengguna & Peran         | Pengelolaan akun pengguna, peran (admin, operator, pimpinan), dan hak akses | Tetap              |
| F-02   | Konfigurasi Unit Layanan           | Pengaturan unit/layanan yang akan disurvei beserta deskripsinya             | Tetap              |
| F-03   | Konfigurasi Periode Survei         | Penetapan periode, tanggal buka-tutup survei                                | Tetap              |
| F-04   | Manajemen Kuesioner                | Pengelolaan 9 unsur wajib IKM sesuai PermenPANRB 14/2017                    | Tetap              |
| F-05   | Pengisian Survei (Publik)          | Antarmuka masyarakat untuk mengisi kuesioner tanpa login, responsif         | Tetap              |
| F-06   | Kalkulasi IKM Otomatis             | Penghitungan NRR, NRR Tertimbang, dan Nilai IKM - kini berbasis queue       | Diperbarui         |
| F-07   | Dashboard Analitik (Internal)      | Visualisasi grafik tren IKM, distribusi jawaban, rekapitulasi               | Tetap              |
| F-08   | Laporan PDF                        | Cetak laporan IKM resmi per periode/unit via DomPDF 3.0                     | Diperbarui         |
| F-09   | Ekspor Data Excel/CSV              | Ekspor data mentah responden dan rekap IKM via PhpSpreadsheet 2.x           | Diperbarui         |
| F-10   | Manajemen Responden                | Pencatatan data responden (opsional/anonim) dan validasi duplikasi          | Diperbarui UU PDP  |
| F-11   | Notifikasi & Audit Log             | Pencatatan aktivitas sistem, notifikasi admin, riwayat perubahan; WORM      | Diperbarui         |
| F-12   | Pengaturan Sistem                  | Konfigurasi nama instansi, logo, footer laporan, parameter global           | Tetap              |
| F-13   | Homepage / Landing Page Publik     | Halaman awal publik sebelum mengisi survei                                  | Tetap              |
| F-14   | QR Code Generator                  | Generate QR Code modern (SVG/PNG/PDF) dengan UTM tracking                   | Diperbarui Endroid |
| F-15   | Notifikasi & Reminder Survei       | Notifikasi multi-channel: in-app, email, WhatsApp Business API              | Diperbarui         |
| F-16   | Manajemen Saran & Pengaduan        | Pengelolaan saran/masukan responden dengan workflow tindak lanjut           | Tetap              |
| F-17   | Analisis Gap & Alert Penurunan IKM | Deteksi penurunan IKM antar periode; alert dashboard                        | Tetap              |
| F-18   | Multi-Bahasa Antarmuka Publik      | Dukungan multi-bahasa berbasis database (bukan file)                        | Diperbarui         |
| F-19   | Preview Kuesioner                  | Admin melihat pratinjau tampilan kuesioner sebelum periode diaktifkan       | Tetap              |
| F-20   | Dashboard Publik Transparansi IKM  | Halaman publik read-only, SEO-optimized, JSON-LD structured data            | Diperbarui         |
| F-21   | Backup & Restore Database          | Backup robust: Percona XtraBackup hot backup + S3 offsite + enkripsi        | Diperbarui         |
| F-22   | Integrasi SSO / LDAP (Opsional)    | OAuth 2.0, OpenID Connect, SAML 2.0, LDAP dengan fallback lokal             | Diperbarui         |
| F-23   | Kelola Privacy & Consent (UU PDP)  | Consent management, DPO dashboard, hak subjek data, compliance report       | BARU               |
| F-24   | Kelola Queue Jobs & Monitoring     | Dashboard queue Redis, worker management, retry, scaling                    | BARU               |
| F-25   | Akses REST API Publik              | Endpoint API publik dengan rate limiting, API key, OpenAPI 3.0 spec         | BARU               |
| F-26   | Kelola Container & CI/CD Pipeline  | Docker, GitHub Actions, deployment blue-green, health check                 | BARU               |

## **2.3 Karakteristik Pengguna**

| **Peran**                     | **Tingkat Teknis** | **Hak Akses & Tanggung Jawab**                                                                                              |
| ----------------------------- | ------------------ | --------------------------------------------------------------------------------------------------------------------------- |
| Super Admin                   | Tinggi             | Akses penuh: manajemen pengguna, konfigurasi global, semua laporan, backup/restore data, audit log, queue monitoring, CI/CD |
| Admin Instansi                | Menengah           | Konfigurasi survei, manajemen periode, pengelolaan kuesioner, QR Code, ekspor laporan, manajemen saran                      |
| Operator Unit                 | Rendah-Menengah    | Input data survei manual, monitoring pengisian, cetak laporan unit sendiri, tanggapan saran unit                            |
| Pimpinan / Viewer             | Rendah             | Hanya dapat melihat dashboard, grafik analitik, alert penurunan IKM, dan laporan (read-only)                                |
| DPO (Data Protection Officer) | Tinggi             | Konfigurasi privacy policy, consent management, data retention, hak subjek data, compliance report \[BARU\]                 |
| DevOps                        | Sangat Tinggi      | Docker/Kubernetes management, CI/CD pipeline, monitoring infrastruktur \[BARU\]                                             |
| Masyarakat / Responden        | Rendah             | Mengisi kuesioner survei melalui halaman publik tanpa login; mengakses landing page dan dashboard publik                    |

## **2.4 Asumsi dan Ketergantungan**

- Sistem dijalankan pada server dengan PHP minimal versi 8.2 dan MySQL 8.0 / MariaDB 10.6
- Docker dan Docker Compose tersedia untuk development environment
- Redis 7.x tersedia untuk cache, session, dan queue
- Server web menggunakan Nginx 1.24+ sebagai reverse proxy
- Library open-source (Bootstrap, DataTables, SweetAlert, DomPDF, Endroid QR Code) sepenuhnya gratis
- Percona XtraBackup tersedia di server database untuk hot backup
- S3-compatible storage (MinIO/AWS S3/Backblaze) tersedia untuk offsite backup
- WhatsApp Business API provider tersedia dan telah mendapat persetujuan Meta (opsional)
- Ketersediaan server dijamin oleh tim infrastruktur TI instansi dengan SLA minimal 99.9%

# **3\. KEBUTUHAN FUNGSIONAL**

## **3.1 Deskripsi Aktor dan Use Case**

| **UC** | **Nama Use Case**                          | **Aktor Terkait**                      |
| ------ | ------------------------------------------ | -------------------------------------- |
| UC-01  | Login Sistem                               | Super Admin, Admin, Operator, Pimpinan |
| UC-02  | Kelola Pengguna                            | Super Admin                            |
| UC-03  | Kelola Unit Layanan                        | Super Admin, Admin                     |
| UC-04  | Kelola Periode Survei                      | Super Admin, Admin                     |
| UC-05  | Kelola Kuesioner & Unsur                   | Super Admin, Admin                     |
| UC-06  | Isi Kuesioner Survei (Publik)              | Masyarakat / Responden                 |
| UC-07  | Input Survei Manual                        | Operator                               |
| UC-08  | Lihat Dashboard & Analitik (Internal)      | Super Admin, Admin, Operator, Pimpinan |
| UC-09  | Lihat & Cetak Laporan PDF                  | Super Admin, Admin, Operator, Pimpinan |
| UC-10  | Ekspor Data Excel/CSV                      | Super Admin, Admin                     |
| UC-11  | Kelola Pengaturan Sistem                   | Super Admin                            |
| UC-12  | Lihat Audit Log                            | Super Admin                            |
| UC-13  | Akses Homepage / Landing Page              | Masyarakat / Responden (publik)        |
| UC-14  | Generate & Cetak QR Code                   | Super Admin, Admin, Operator           |
| UC-15  | Kelola Notifikasi & Reminder               | Super Admin, Admin                     |
| UC-16  | Kelola Saran & Pengaduan                   | Admin, Operator                        |
| UC-17  | Lihat Analisis Gap & Alert IKM             | Super Admin, Admin, Pimpinan           |
| UC-18  | Atur Multi-Bahasa                          | Super Admin, Admin                     |
| UC-19  | Preview Kuesioner                          | Super Admin, Admin                     |
| UC-20  | Akses Dashboard Publik Transparansi        | Masyarakat (publik)                    |
| UC-21  | Backup & Restore Database                  | Super Admin                            |
| UC-22  | Konfigurasi SSO / LDAP                     | Super Admin                            |
| UC-23  | Kelola Privacy & Consent (UU PDP) \[BARU\] | Super Admin, DPO                       |
| UC-24  | Kelola Queue Jobs & Monitoring \[BARU\]    | Super Admin, DevOps                    |
| UC-25  | Akses REST API Publik \[BARU\]             | Masyarakat / Third-party Applications  |
| UC-26  | Kelola Container & CI/CD Pipeline \[BARU\] | Super Admin, DevOps                    |

## **3.2 Spesifikasi Use Case Detail**

### **3.2.1 UC-06: Isi Kuesioner Survei (Publik)**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-06                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Nama**             | Isi Kuesioner Survei (Publik)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| **Aktor Utama**      | Masyarakat / Responden                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| **Trigger**          | Responden memilih unit layanan dari Landing Page, memindai QR Code, atau membuka URL survei langsung                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| **Pra-kondisi**      | Periode survei sedang aktif; kuesioner telah dikonfigurasi; unit layanan tersedia                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| **Alur Normal**      | 1\. Responden datang dari Landing Page atau QR Code 2. Sistem menampilkan consent form UU PDP 3. Responden memberikan consent (opt-in eksplisit) 4. Sistem menampilkan halaman survei responsif 5. Responden memilih unit layanan (jika belum dipilih) 6. Sistem menampilkan 9 unsur kuesioner wajib IKM 7. Responden memilih jawaban (skala 1-4) per unsur 8. Responden mengisi data demografis (opsional) 9. Responden mengisi saran/masukan (opsional) 10. Validasi berjalan di sisi klien 11. Responden klik tombol 'Kirim' 12. Sistem menampilkan konfirmasi SweetAlert 13. Data tersimpan; consent direkam; job IKM calculation masuk queue |
| **Alur Alternatif**  | A1: Validasi gagal - pesan error ditampilkan A2: Periode tidak aktif - pesan 'Survei Tidak Aktif' A3: Koneksi terputus - data tersimpan di localStorage sementara A4: Responden menolak consent - tidak dapat melanjutkan survei                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **Post-kondisi**     | Data survei tersimpan; consent direkam di tb_consent_log; saran masuk ke modul manajemen saran; job ikm-calculation masuk Redis queue                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Kebutuhan Khusus** | Responsif 320px-3840px; load < 3 detik; antarmuka tersedia dalam bahasa yang dikonfigurasi; consent opt-in eksplisit (tidak pre-checked); WCAG 2.1 AA                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |

### **3.2.2 UC-13: Akses Homepage / Landing Page**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                 |
| -------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-13                                                                                                                                                                                                                                                                                                                                                                                         |
| **Nama**             | Akses Homepage / Landing Page                                                                                                                                                                                                                                                                                                                                                                 |
| **Aktor Utama**      | Masyarakat / Responden (tanpa login)                                                                                                                                                                                                                                                                                                                                                          |
| **Trigger**          | Responden membuka URL utama aplikasi di browser atau memindai QR Code instansi                                                                                                                                                                                                                                                                                                                |
| **Pra-kondisi**      | Minimal satu unit layanan aktif dan satu periode survei aktif tersedia                                                                                                                                                                                                                                                                                                                        |
| **Alur Normal**      | 1\. Sistem menampilkan halaman landing page dengan CDN caching 2. Halaman menampilkan: nama & logo instansi, deskripsi layanan, daftar unit layanan aktif 3. Tersedia tombol 'Isi Survei' per unit layanan aktif 4. Tersedia tautan ke Dashboard Publik Transparansi IKM 5. Responden memilih unit layanan dan diteruskan ke UC-06 6. Tersedia pilihan bahasa (jika fitur multi-bahasa aktif) |
| **Alur Alternatif**  | A1: Tidak ada periode aktif - halaman menampilkan pesan 'Tidak ada survei aktif saat ini' A2: Semua unit nonaktif - hanya menampilkan info instansi dan hasil survei terakhir                                                                                                                                                                                                                 |
| **Post-kondisi**     | Responden diarahkan ke halaman kuesioner unit layanan yang dipilih                                                                                                                                                                                                                                                                                                                            |
| **Kebutuhan Khusus** | Halaman harus dapat dimuat tanpa JavaScript (progressive enhancement); load < 1.5 detik dengan CDN; responsif penuh; SEO-optimized dengan JSON-LD dan sitemap.xml                                                                                                                                                                                                                             |

### **3.2.3 UC-14: Generate & Cetak QR Code**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-14                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Nama**             | Generate & Cetak QR Code                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| **Aktor Utama**      | Super Admin, Admin, Operator                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Trigger**          | Admin membuka menu 'QR Code' dari sidebar atau dari halaman detail periode/unit layanan                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| **Pra-kondisi**      | Admin telah login; unit layanan dan periode tersedia                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| **Alur Normal**      | 1\. Admin memilih unit layanan dan/atau periode survei 2. Admin memilih format output: SVG, PNG, atau PDF 3. Admin memilih ukuran preset: S/M/L/XL 4. Admin dapat menambahkan logo instansi di tengah QR Code 5. Sistem men-generate QR Code menggunakan Endroid QR Code 5.x 6. Sistem membuat short URL dengan UTM tracking 7. Admin dapat menambahkan teks label di bawah QR Code 8. Sistem menampilkan preview QR Code 9. Admin mengunduh QR Code dalam format yang dipilih 10. Tersedia opsi cetak langsung dengan layout siap tempel 11. Scan count dan last_scan_at direkam untuk analytics |
| **Post-kondisi**     | File QR Code tersimpan di tb_qr_code; short URL aktif; dapat diunduh/dicetak untuk distribusi offline                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Kebutuhan Khusus** | QR Code harus dapat dipindai pada cetakan ukuran A5 ke atas; mendukung logo center; output SVG untuk kualitas cetak; library Endroid QR Code 5.x tanpa API eksternal                                                                                                                                                                                                                                                                                                                                                                                                                              |

### **3.2.4 UC-15: Kelola Notifikasi & Reminder Survei**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-15                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Nama**             | Kelola Notifikasi & Reminder Survei                                                                                                                                                                                                                                                                                                                                                                                                                               |
| **Aktor Utama**      | Super Admin, Admin                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| **Trigger**          | Otomatis oleh scheduler (cron job) atau manual melalui menu Pengaturan Notifikasi                                                                                                                                                                                                                                                                                                                                                                                 |
| **Alur Normal**      | 1\. Admin mengkonfigurasi aturan notifikasi: threshold hari sebelum periode berakhir, threshold % target responden 2. Scheduler memeriksa kondisi setiap jam via Redis queue 3. Jika kondisi terpenuhi, sistem membuat job notification di queue 4. Queue worker memproses: kirim notifikasi in-app, email, dan/atau WhatsApp Business API 5. Notifikasi tampil di header dashboard dengan badge jumlah 6. Admin dapat menandai notifikasi sebagai 'sudah dibaca' |
| **Jenis Notifikasi** | \- Periode akan berakhir dalam N hari - Target responden belum tercapai (< X%) - IKM turun di bawah threshold (terhubung UC-17) - Periode baru dibuat/diaktifkan - Backup database berhasil/gagal - Queue backlog melebihi threshold                                                                                                                                                                                                                              |
| **Post-kondisi**     | Notifikasi tersimpan di tabel tb_notifikasi; email/WhatsApp terkirim melalui queue worker                                                                                                                                                                                                                                                                                                                                                                         |

### **3.2.5 UC-23: Kelola Privacy & Consent (UU PDP) \[BARU\]**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| -------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-23                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| **Nama**             | Kelola Privacy & Consent (UU PDP)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Aktor Utama**      | Super Admin, DPO (Data Protection Officer)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| **Trigger**          | DPO/Super Admin membuka menu Sistem > Privacy & Consent                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **Pra-kondisi**      | Super Admin telah login; kebijakan privasi instansi telah disusun; DPO telah ditunjuk                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| **Alur Normal**      | 1\. DPO mengkonfigurasi kebijakan privasi: tujuan pengumpulan data, jenis data dikumpulkan, dasar hukum (consent/contract/legal obligation), retention period, pihak ketiga yang menerima data 2. DPO mengkonfigurasi consent form: bahasa, checkbox style (opt-in eksplisit), link ke kebijakan privasi lengkap 3. DPO mengatur data retention policy: auto-delete setelah X bulan, anonymization vs deletion 4. DPO mengatur hak subjek data: form permintaan akses data, form permintaan penghapusan (right to erasure), form permintaan koreksi data 5. Sistem menampilkan dashboard compliance: jumlah consent diberikan, permintaan hak subjek data, status penanganan 6. DPO dapat mengekspor laporan compliance untuk audit regulator |
| **Bisnis Rule**      | BR-23-01: Consent harus opt-in eksplisit (tidak boleh pre-checked checkbox) BR-23-02: Responden dapat menarik consent kapan saja tanpa konsekuensi negatif BR-23-03: Data pribadi dihapus atau dianonimkan setelah retention period BR-23-04: Permintaan hak subjek data harus ditangani dalam 30 hari kerja BR-23-05: Breach notification ke regulator dalam 3x24 jam jika terjadi kebocoran data                                                                                                                                                                                                                                                                                                                                            |
| **Kebutuhan Khusus** | Consent record disimpan terpisah dengan timestamp, IP, dan versi kebijakan; audit trail tidak dapat dihapus; enkripsi data pribadi at rest (AES-256-GCM); pseudonymization untuk analytics                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |

### **3.2.6 UC-24: Kelola Queue Jobs & Monitoring \[BARU\]**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                              |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **ID Use Case**      | UC-24                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Nama**             | Kelola Queue Jobs & Monitoring                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Aktor Utama**      | Super Admin, DevOps                                                                                                                                                                                                                                                                                                                                                                                                                        |
| **Trigger**          | Super Admin membuka menu Sistem > Queue Monitor                                                                                                                                                                                                                                                                                                                                                                                            |
| **Alur Normal**      | 1\. Sistem menampilkan dashboard queue: jobs pending, processing, failed, completed 2. Sistem menampilkan worker status: active workers, uptime, memory usage, throughput 3. Super Admin dapat retry failed jobs (individual atau batch) 4. Super Admin dapat pause/resume queue 5. Super Admin dapat configure worker scaling (max concurrent jobs) 6. Alert otomatis jika queue backlog > threshold (misal: 1000 jobs pending > 5 menit) |
| **Jenis Job Queue**  | \- ikm-calculation: Kalkulasi IKM per periode - notification: Email, WhatsApp, in-app - backup: Database backup (full, incremental) - report: PDF/Excel generation - export: Data export besar - cleanup: Data retention enforcement, log rotation                                                                                                                                                                                         |
| **Kebutuhan Khusus** | Redis 7.x dengan persistence AOF; job idempotency key untuk mencegah duplikat; dead letter queue untuk job yang gagal > 5x retry; monitoring dengan Prometheus/Grafana metrics                                                                                                                                                                                                                                                             |

### **3.2.7 UC-25: Akses REST API Publik \[BARU\]**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                     |
| -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-25                                                                                                                                                                                                                                                                                                             |
| **Nama**             | Akses REST API Publik                                                                                                                                                                                                                                                                                             |
| **Aktor Utama**      | Masyarakat / Third-party Applications                                                                                                                                                                                                                                                                             |
| **Trigger**          | HTTP request ke endpoint API publik                                                                                                                                                                                                                                                                               |
| **Endpoint Publik**  | GET /api/v1/landing - Data landing page GET /api/v1/unit-layanan - Daftar unit layanan aktif GET /api/v1/survei/{id_unit} - Form survei (kuesioner) POST /api/v1/survei/{id_unit} - Submit jawaban GET /api/v1/transparansi - Dashboard transparansi IKM GET /api/v1/transparansi/{id_unit} - Detail IKM per unit |
| **Autentikasi**      | API Key (read-only endpoints) atau Anonymous (submit survei) Rate limiting: 100 req/minute per IP/API Key                                                                                                                                                                                                         |
| **Response Format**  | JSON dengan HTTP status codes; pagination dengan cursor-based; caching headers (ETag, Cache-Control)                                                                                                                                                                                                              |
| **Kebutuhan Khusus** | CORS configured untuk domain instansi; JSON Schema validation; API versioning (v1, v2); OpenAPI 3.0 documentation; no sensitive data in error messages                                                                                                                                                            |

### **3.2.8 UC-26: Kelola Container & CI/CD Pipeline \[BARU\]**

| **Atribut**          | **Deskripsi**                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| -------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID Use Case**      | UC-26                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| **Nama**             | Kelola Container & CI/CD Pipeline                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| **Aktor Utama**      | Super Admin, DevOps                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| **Trigger**          | Push ke repository Git atau manual trigger                                                                                                                                                                                                                                                                                                                                                                                                                            |
| **Alur Normal**      | 1\. Developer push code ke branch develop atau main 2. GitHub Actions/GitLab CI trigger: a. Run PHPUnit tests b. Run PHPStan static analysis c. Run OWASP dependency check d. Build Docker image (PHP 8.2-FPM + Nginx + CI4) e. Push image ke container registry 3. Deployment ke staging environment (automated) 4. UAT approval untuk production 5. Deployment ke production dengan blue-green atau rolling update 6. Health check dan rollback otomatis jika gagal |
| **Docker Services**  | app: PHP 8.2-FPM + CI4 web: Nginx reverse proxy db: MySQL 8.0 / MariaDB 10.6 redis: Redis 7.x (cache, queue, session) queue-worker: CI4 queue worker (scalable replicas) scheduler: CI4 scheduler (cron alternative)                                                                                                                                                                                                                                                  |
| **Kebutuhan Khusus** | Docker Compose untuk development; Kubernetes untuk production orchestration; secrets management dengan Docker Secrets atau Kubernetes Secrets; health check endpoints /health, /ready; log aggregation dengan Fluentd/Fluent Bit                                                                                                                                                                                                                                      |

# **4\. KEBUTUHAN NON-FUNGSIONAL**

| **ID** | **Kategori**      | **Parameter**      | **Target / Kriteria Penerimaan v2.0.0**                                                                                              |
| ------ | ----------------- | ------------------ | ------------------------------------------------------------------------------------------------------------------------------------ |
| NF-01  | Kinerja           | Response Time      | Halaman dashboard dan daftar data: < 2 detik pada jaringan LAN 1 Gbps                                                                |
| NF-02  | Kinerja           | Throughput         | Sistem mampu melayani minimal 500 pengguna konkuren tanpa degradasi (dari 100)                                                       |
| NF-03  | Kinerja           | Kalkulasi IKM      | Kalkulasi IKM dari 10.000 responden selesai dalam waktu < 5 detik via queue worker (dari 1.000 real-time)                            |
| NF-04  | Kinerja           | Landing Page Load  | < 1.5 detik pada koneksi 4G dengan CDN; < 1 detik dengan cache hit                                                                   |
| NF-05  | Ketersediaan      | Uptime             | SLA 99.9% per bulan (downtime terjadwal maksimal 43.8 menit/bulan, dari 99%)                                                         |
| NF-06  | Keamanan          | Autentikasi        | JWT dengan RS256 (asymmetric), refresh token rotation, session timeout 30 menit, MFA untuk Super Admin                               |
| NF-07  | Keamanan          | SQL Injection      | Seluruh query menggunakan CI4 Query Builder dengan parameter binding; prepared statements wajib                                      |
| NF-08  | Keamanan          | XSS Protection     | Output di-encode htmlspecialchars dengan ENT_QUOTES\|ENT_SUBSTITUTE; CSP header ketat; HTML Purifier untuk rich text                 |
| NF-09  | Keamanan          | Akses Tidak Sah    | HTTP 401/403 dengan logging; rate limiting per endpoint; IP whitelist untuk admin endpoints                                          |
| NF-10  | Keamanan          | Data Enkripsi      | Data pribadi at rest: AES-256-GCM; in transit: TLS 1.3; backup: AES-256-GCM + HSM/env key                                            |
| NF-11  | Keamanan          | API Security       | Rate limiting 100 req/min; API key rotation setiap 90 hari; scope-based access control                                               |
| NF-12  | Usabilitas        | Responsivitas      | Antarmuka responsif pada resolusi 320px s.d. 3840px (4K) menggunakan Bootstrap 5 grid                                                |
| NF-13  | Usabilitas        | Kemudahan Survei   | Masyarakat dapat menyelesaikan pengisian survei dalam < 4 menit (dari 5 menit)                                                       |
| NF-14  | Usabilitas        | Landing Page       | Responden dapat menemukan dan membuka form survei dalam < 2 langkah/klik (dari 3)                                                    |
| NF-15  | Pemeliharaan      | Code Standard      | PSR-12 (PHP), ESLint (JavaScript), PHPStan level 8, Rector untuk automated refactoring                                               |
| NF-16  | Portabilitas      | Browser Support    | Chrome 110+, Firefox 110+, Edge 110+, Safari 16+, Android Chrome 110+, iOS Safari 16+                                                |
| NF-17  | Skalabilitas      | Data Growth        | Sistem mampu menangani akumulasi hingga 5 juta record responden (dari 500.000) dengan partitioning                                   |
| NF-18  | Skalabilitas      | Horizontal Scaling | Sistem dapat di-scale horizontally dengan load balancer dan stateless application servers                                            |
| NF-19  | Auditabilitas     | Audit Trail        | Setiap aksi CRUD tercatat di tabel audit_log dengan user, waktu, IP, user agent, data lama, data baru; append-only, WORM storage     |
| NF-20  | Ketersediaan Data | Backup             | Hot backup dengan RPO < 6 jam, RTO < 15 menit untuk 5GB; offsite replication                                                         |
| NF-21  | Aksesibilitas     | WCAG               | Landing page dan halaman survei publik memenuhi standar WCAG 2.1 Level AA                                                            |
| NF-22  | SEO               | Dashboard Publik   | Halaman dashboard publik dan landing page dapat diindeks mesin pencari (meta tags, JSON-LD structured data, sitemap.xml, robots.txt) |
| NF-23  | Privasi           | UU PDP Compliance  | Consent management, data minimization, purpose limitation, storage limitation, integrity and confidentiality, accountability         |
| NF-24  | DevOps            | CI/CD              | Deployment otomatis < 10 menit dari push ke production; rollback < 5 menit                                                           |
| NF-25  | Monitoring        | Observability      | Metrics (Prometheus), logging (ELK/Loki), tracing (Jaeger/Zipkin) untuk seluruh komponen                                             |

# **5\. ARSITEKTUR DAN DESAIN SISTEM v2.0.0**

## **5.1 Arsitektur Modern (Microservices-Ready Monolith)**

CDN (CloudFlare/Static) - Asset Statis, DDoS Protection, WAF

|

Load Balancer (Nginx/HAProxy) - SSL Termination, Rate Limiting, Routing

| | |

Web 1 (CI4) Web 2 (CI4) Web N (CI4) \[Stateless\]

| | |

+--------------------+--------------------+

| | |

Redis Cache Redis Queue MySQL 8.0/MariaDB (Partitioned)

\+ Session + Pub/Sub

| | |

+----------------+----------------+

|

S3-Compatible Storage (MinIO/AWS S3) - Backup, Uploads

## **5.2 Stack Teknologi v2.0.0**

### **5.2.1 Backend**

- PHP >= 8.2 (direkomendasikan PHP 8.3 dengan JIT)
- CodeIgniter 4.4.x - framework MVC modern dengan namespace, middleware, service container, dan entity
- DomPDF 3.0.x - library generasi PDF dari HTML
- PhpSpreadsheet 2.x - library ekspor Excel
- Endroid QR Code 5.x - library generasi QR Code modern (SVG, PNG, logo center, styling) \[BARU\]
- php-ldap extension - untuk integrasi LDAP (opsional)
- league/oauth2-client - untuk OAuth 2.0 / OpenID Connect \[BARU\]
- onelogin/php-saml - untuk SAML 2.0 integration \[BARU\]
- predis/predis - PHP client untuk Redis \[BARU\]
- codeigniter4/queue - atau custom queue library dengan Redis \[BARU\]
- Composer untuk manajemen dependency

### **5.2.2 Frontend**

- HTML5 & CSS3 dengan CSS Custom Properties (design tokens)
- Bootstrap 5.3.x - framework CSS responsif
- Vanilla JavaScript (ES6+) untuk publik page (reduksi bundle size) \[DIPERBARUI\]
- jQuery 3.7.x - hanya untuk admin panel (legacy compatibility)
- DataTables 1.13.x dengan mode server-side processing
- SweetAlert2 11.x - library dialog/notifikasi
- jQuery Validate 1.20.x - validasi form
- Chart.js 4.x - visualisasi grafik IKM
- Font Awesome 6.x - ikon UI
- Vite - build tool untuk bundling dan minification \[BARU\]

### **5.2.3 Database & Storage**

- MySQL 8.0 / MariaDB 10.6
- Partitioning: RANGE partitioning pada tb_survei_jawaban berdasarkan id_periode \[BARU\]
- Charset: utf8mb4 (mendukung emoji dan karakter unicode penuh)
- Storage Engine: InnoDB dengan innodb_file_per_table
- Redis 7.x - cache, session, queue, rate limiting \[BARU\]
- S3-compatible Storage - offsite backup, file uploads \[BARU\]

### **5.2.4 Infrastructure & DevOps**

- Docker & Docker Compose - development environment \[BARU\]
- Kubernetes - production orchestration (opsional, recommended) \[BARU\]
- Nginx 1.24+ - reverse proxy, static file serving, SSL termination
- PHP-FPM - PHP process manager dengan dynamic pool
- GitHub Actions / GitLab CI - CI/CD pipeline \[BARU\]
- Prometheus + Grafana - metrics dan monitoring \[BARU\]
- ELK Stack atau Loki - centralized logging \[BARU\]

# **6\. DESAIN BASIS DATA v2.0.0**

## **6.1 Perubahan Schema Utama**

| **Tabel**         | **Perubahan**                                                                  | **Alasan**                                 |
| ----------------- | ------------------------------------------------------------------------------ | ------------------------------------------ |
| tb_survei_jawaban | RANGE Partitioning per id_periode                                              | Performa query per periode, archival mudah |
| tb_responden      | Tambah: consent_given, consent_timestamp, consent_version, data_retention_date | UU PDP compliance                          |
| tb_audit_log      | PARTITION BY RANGE (YEAR(created_at))                                          | Tabel besar, query history per tahun       |
| tb_notifikasi     | Tambah: channel, external_id, delivery_status, retry_count                     | Multi-channel tracking                     |
| tb_backup_log     | Tambah: storage_target, checksum_sha256, encryption_key_id, rpo_verified       | Robust backup tracking                     |
| tb_bahasa         | Redesign: key, locale, value, module, is_cached                                | Database-driven translation                |
| tb_qr_code        | Tabel baru                                                                     | Tracking QR Code scans                     |
| tb_consent_log    | Tabel baru                                                                     | UU PDP consent audit trail                 |
| tb_queue_jobs     | Tabel baru (fallback)                                                          | Queue persistence jika Redis down          |
| tb_api_keys       | Tabel baru                                                                     | API authentication untuk third-party       |

## **6.2 Tabel Baru: tb_consent_log (UU PDP)**

| **Kolom**       | **Tipe Data**                                  | **Null** | **Default**          | **Keterangan**                         |
| --------------- | ---------------------------------------------- | -------- | -------------------- | -------------------------------------- |
| id_consent      | BIGINT PK AI                                   | No       | -                    | Primary key                            |
| id_responden    | BIGINT                                         | Ya       | NULL                 | FK ke tb_responden (NULL untuk anonim) |
| consent_type    | ENUM('survei','demografi','saran','publikasi') | No       | -                    | Jenis consent                          |
| consent_given   | TINYINT(1)                                     | No       | 0                    | 1=ya, 0=tidak                          |
| consent_version | VARCHAR(20)                                    | No       | -                    | Versi kebijakan privasi                |
| ip_address      | VARBINARY(16)                                  | No       | -                    | IP address (IPv6 compatible)           |
| user_agent_hash | VARCHAR(64)                                    | No       | -                    | SHA-256 hash user agent                |
| timestamp       | DATETIME(6)                                    | No       | CURRENT_TIMESTAMP(6) | Waktu consent dengan microsecond       |
| withdrawal_date | DATETIME                                       | Ya       | NULL                 | Waktu penarikan consent                |

## **6.3 Tabel Baru: tb_qr_code**

| **Kolom**    | **Tipe Data**           | **Null** | **Default**       | **Keterangan**                |
| ------------ | ----------------------- | -------- | ----------------- | ----------------------------- |
| id_qr        | BIGINT PK AI            | No       | -                 | Primary key                   |
| id_unit      | INT(11)                 | No       | -                 | FK ke tb_unit_layanan         |
| id_periode   | INT(11)                 | No       | -                 | FK ke tb_periode              |
| short_url    | VARCHAR(100)            | No       | -                 | Short URL dengan UTM tracking |
| qr_data      | TEXT                    | No       | -                 | Data QR Code (SVG/PNG path)   |
| format       | ENUM('svg','png','pdf') | No       | 'svg'             | Format output                 |
| size_preset  | ENUM('S','M','L','XL')  | No       | 'M'               | Ukuran preset                 |
| scan_count   | BIGINT                  | No       | 0                 | Jumlah scan (analytics)       |
| last_scan_at | DATETIME                | Ya       | NULL              | Waktu scan terakhir           |
| created_by   | INT(11)                 | No       | -                 | FK ke tb_pengguna             |
| created_at   | TIMESTAMP               | No       | CURRENT_TIMESTAMP | -                             |

## **6.4 Tabel Lama yang Diperluas (v1.1.0)**

### **6.4.1 Tabel: tb_saran**

| **Kolom**          | **Tipe Data**                   | **Null** | **Default** | **Keterangan**                                     |
| ------------------ | ------------------------------- | -------- | ----------- | -------------------------------------------------- |
| id_saran           | INT(11) PK AI                   | No       | -           | Primary key saran                                  |
| id_periode         | INT(11)                         | No       | -           | FK ke tb_periode                                   |
| id_responden       | INT(11)                         | Ya       | NULL        | FK ke tb_responden (opsional/anonim)               |
| isi_saran          | TEXT                            | No       | -           | Isi saran/masukan responden (sudah disanitasi XSS) |
| status             | ENUM('baru','proses','selesai') | No       | 'baru'      | Status tindak lanjut saran                         |
| tanggapan          | TEXT                            | Ya       | NULL        | Tanggapan admin/operator terhadap saran            |
| id_pengguna_respon | INT(11)                         | Ya       | NULL        | FK ke tb_pengguna (siapa yang merespons)           |
| tanggal_respon     | DATETIME                        | Ya       | NULL        | Waktu tanggapan diberikan                          |
| created_at         | TIMESTAMP                       | No       | CURRENT     | Waktu saran masuk                                  |

### **6.4.2 Tabel: tb_rekap_ikm (Kolom Baru)**

| **Kolom Baru** | **Tipe Data** | **Null** | **Default** | **Keterangan**                                                         |
| -------------- | ------------- | -------- | ----------- | ---------------------------------------------------------------------- |
| delta_ikm      | DECIMAL(5,2)  | Ya       | NULL        | \[BARU\] Selisih nilai IKM vs periode sebelumnya (negatif = penurunan) |
| flag_alert     | TINYINT(1)    | No       | 0           | \[BARU\] 1 jika penurunan IKM melebihi threshold alert                 |
| is_published   | TINYINT(1)    | No       | 0           | \[BARU\] 1 jika periode ini ditampilkan di dashboard publik            |
| published_at   | DATETIME      | Ya       | NULL        | \[BARU\] Waktu dipublikasikan ke dashboard publik                      |

# **7\. ALGORITMA KALKULASI IKM v2.0.0**

## **7.1 Formula (Tidak Berubah dari v1.1.0)**

Kalkulasi tetap mengacu sepenuhnya pada PermenPANRB Nomor 14 Tahun 2017.

| **Komponen**             | **Formula / Nilai**                                               |
| ------------------------ | ----------------------------------------------------------------- |
| Jumlah Unsur             | 9 unsur wajib (U-1 s.d. U-9)                                      |
| Bobot per Unsur (b)      | b = 1/9 = 0,111                                                   |
| NRR per Unsur            | NRR(i) = Σ nilai jawaban unsur ke-i / jumlah responden unsur ke-i |
| NRR Tertimbang per Unsur | NRR_t(i) = NRR(i) × 0,111                                         |
| IKM Total                | IKM = Σ NRR_t(i) × 25 (konversi ke skala 25-100)                  |
| Mutu Layanan A           | IKM 88,31 - 100,00 → Sangat Baik                                  |
| Mutu Layanan B           | IKM 76,61 - 88,30 → Baik                                          |
| Mutu Layanan C           | IKM 65,00 - 76,60 → Kurang Baik                                   |
| Mutu Layanan D           | IKM 25,00 - 64,99 → Tidak Baik                                    |

## **7.2 Formula Delta IKM (Analisis Gap)**

| **Komponen**        | **Formula / Nilai**                                                         |
| ------------------- | --------------------------------------------------------------------------- |
| Delta IKM Total     | Δ IKM = IKM(periode_n) − IKM(periode_n−1)                                   |
| Delta NRR per Unsur | Δ NRR(i) = NRR(i, periode_n) − NRR(i, periode_n−1)                          |
| Flag Alert          | flag_alert = 1 jika Δ IKM < −threshold_alert (default: −5 poin)             |
| Unsur Kritis        | Unsur ditandai kritis jika Δ NRR(i) < −threshold_unsur (default: −0,5 poin) |

## **7.3 Trigger Kalkulasi (DIPERBARUI v2.0.0)**

| **Mode**    | **Trigger**                   | **Implementasi**                                         |
| ----------- | ----------------------------- | -------------------------------------------------------- |
| Real-time   | Data jawaban baru disimpan    | Dihapus - tidak scalable                                 |
| Queue-based | Event survei.submitted        | Default - Redis queue job ikm-calculation                |
| Manual      | Admin klik 'Hitung Ulang IKM' | Queue job dengan priority high                           |
| Scheduled   | Cron job setiap jam           | Queue job untuk periode yang aktif                       |
| Batch       | Periode berstatus 'Selesai'   | Queue job untuk seluruh periode dengan delta calculation |

# **8\. KEAMANAN SISTEM v2.0.0**

## **8.1 Autentikasi Modern**

| **Metode** | **Protokol**               | **Fallback** | **Keterangan**                                                    |
| ---------- | -------------------------- | ------------ | ----------------------------------------------------------------- |
| Primary    | OAuth 2.0 / OpenID Connect | Login lokal  | Identity Provider instansi (Keycloak, Azure AD, Google Workspace) |
| Secondary  | SAML 2.0                   | Login lokal  | Untuk instansi dengan infrastructure SAML                         |
| Tertiary   | LDAP                       | Login lokal  | Legacy support                                                    |
| Lokal      | Username + Password + MFA  | -            | Untuk emergency access, DPO, Super Admin wajib MFA                |

## **8.2 Spesifikasi JWT (RS256)**

Header: alg=RS256, typ=JWT, kid=key-id-2026-01

Payload: sub (user UUID), iss (instansi), aud (ikm-app), iat, exp (30 menit), scope, role, unit\[\]

Refresh token rotation aktif; token blacklist di Redis; MFA wajib untuk Super Admin dan DPO.

## **8.3 Content Security Policy (Ketat)**

default-src 'self'; script-src 'self' 'nonce-{random}' <https://cdn.jsdelivr.net>; style-src 'self' 'nonce-{random}' <https://cdn.jsdelivr.net>; img-src 'self' data: https:; font-src 'self' <https://cdn.jsdelivr.net>; connect-src 'self' <https://api.whatsapp.com>; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;

## **8.4 Perlindungan Data**

- Seluruh query menggunakan CI4 Query Builder dengan parameter binding (prepared statements)
- Output HTML di-encode htmlspecialchars() dengan ENT_QUOTES|ENT_SUBSTITUTE untuk mencegah XSS
- Upload file dibatasi: ekstensi whitelist, maks 10MB, disimpan di luar webroot
- HTTPS wajib di lingkungan produksi; HSTS diaktifkan; TLS 1.3 minimum
- Enkripsi data pribadi at rest: AES-256-GCM; backup: AES-256-GCM + HSM/env key
- Audit trail WORM storage - tidak dapat dihapus; append-only

# **9\. PRIVACY BY DESIGN (UU PDP) v2.0.0**

## **9.1 Prinsip Implementasi**

| **Principle**               | **Implementasi**                                                                                                    |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| Data Minimization           | Hanya kumpulkan: nama (opsional), no telepon (opsional), email (opsional), usia range (opsional), gender (opsional) |
| Purpose Limitation          | Data hanya untuk: kalkulasi IKM, tindak lanjut saran (jika identifikasi diizinkan), transparansi publik (anonim)    |
| Storage Limitation          | Auto-delete setelah 2 tahun; anonymization untuk data historis                                                      |
| Accuracy                    | Responden dapat meminta koreksi data via form DPO                                                                   |
| Integrity & Confidentiality | Enkripsi at rest, TLS 1.3 in transit, access log lengkap                                                            |
| Accountability              | DPO dashboard, compliance report, audit trail WORM                                                                  |

## **9.2 Consent Flow**

- Responden masuk Landing Page
- Display: Tujuan survei, data dikumpulkan, retention period, hak subjek data
- Checkbox 1 (wajib): Saya setuju data saya digunakan untuk kalkulasi IKM
- Checkbox 2 (opsional): Saya setuju dihubungi untuk tindak lanjut
- Checkbox 3 (opsional): Saya setuju data saya dipublikasikan secara anonim
- Tombol submit disabled sampai checkbox 1 dicentang
- Record consent dengan timestamp, versi kebijakan, IP hash di tb_consent_log

# **10\. BACKUP & DISASTER RECOVERY v2.0.0**

## **10.1 Backup Strategy (3-2-1 Rule)**

| **Layer** | **Metode**                             | **Frekuensi**     | **Retention** | **Lokasi**                   |
| --------- | -------------------------------------- | ----------------- | ------------- | ---------------------------- |
| Primary   | Percona XtraBackup (hot backup)        | Harian 02:00 WIB  | 7 hari        | Local SSD                    |
| Secondary | Percona XtraBackup incremental         | Setiap 6 jam      | 48 jam        | Local SSD                    |
| Tertiary  | mysqldump logical + gzip + AES-256-GCM | Harian 03:00 WIB  | 30 hari       | S3-compatible (MinIO/AWS S3) |
| Archive   | Full logical + GPG                     | Mingguan (Minggu) | 1 tahun       | S3 Glacier / Cold Storage    |
| Emergency | Real-time binlog streaming             | Continuous        | 7 hari        | Synchronous replica          |

## **10.2 RPO/RTO Matrix**

| **Skenario**            | **RPO**                | **RTO**  | **Metode Recovery**                       |
| ----------------------- | ---------------------- | -------- | ----------------------------------------- |
| Single table corruption | 0 (binlog)             | 10 menit | Point-in-time recovery dari binlog        |
| Database server failure | 6 jam (incremental)    | 15 menit | Promote replica + apply binlog            |
| Full datacenter failure | 24 jam (daily offsite) | 30 menit | Restore dari S3 + binlog apply            |
| Ransomware/cyber attack | 24 jam                 | 45 menit | Immutable backup (WORM) dari cold storage |

# **11\. IMPLEMENTASI WHATSAPP NOTIFIKASI**

## **11.1 Arsitektur**

CI4 Application → Redis Queue (notification-whatsapp) → Queue Worker (WhatsAppWorker) → WhatsApp Business API Provider → Meta / Official Business Partner → Responden/Admin WhatsApp

## **11.2 Template Messages (Pre-approved by Meta)**

| **Template Name**   | **Bahasa** | **Variabel**                                                            | **Trigger**                     |
| ------------------- | ---------- | ----------------------------------------------------------------------- | ------------------------------- |
| ikm_reminder_period | ID         | {{1}}=nama instansi, {{2}}=nama unit, {{3}}=sisa hari, {{4}}=URL survei | Periode hampir berakhir         |
| ikm_target_not_met  | ID         | {{1}}=nama unit, {{2}}=% terkumpul, {{3}}=target, {{4}}=URL QR          | Target responden belum tercapai |
| ikm_alert_decrease  | ID         | {{1}}=nama unit, {{2}}=IKM lama, {{3}}=IKM baru, {{4}}=delta            | Penurunan IKM signifikan        |
| ikm_backup_success  | ID         | {{1}}=jenis backup, {{2}}=waktu, {{3}}=ukuran file, {{4}}=storage       | Backup berhasil                 |
| ikm_backup_failed   | ID         | {{1}}=jenis backup, {{2}}=waktu, {{3}}=error message, {{4}}=retry link  | Backup gagal                    |

# **12\. DESAIN UI/UX**

## **12.1 Prinsip Desain**

- Responsif - antarmuka wajib berfungsi optimal di semua ukuran layar 320px-3840px (mobile-first)
- Konsisten - palet warna, tipografi, dan komponen UI menggunakan design system Bootstrap 5.3
- Aksesibel - kontras warna memenuhi WCAG 2.1 AA; mendukung keyboard navigation dan screen reader
- Efisien - masyarakat dapat menyelesaikan pengisian survei dalam < 4 menit
- Informatif - hasil IKM ditampilkan secara visual dengan grafik yang mudah dipahami
- Welcoming - Landing page memberikan kesan profesional dan mengundang partisipasi
- Privacy-first - consent form ditampilkan dengan jelas sebelum pengisian survei

## **12.2 Palet Warna dan Tipografi**

| **Elemen**       | **Kode Warna** | **Penggunaan**                                      |
| ---------------- | -------------- | --------------------------------------------------- |
| Primary (Navy)   | #1A3C6E        | Header, sidebar, tombol utama, heading h1           |
| Secondary (Blue) | #2E75B6        | Tombol sekunder, link, aksen heading h2             |
| Accent (Red)     | #C00000        | Indikator kritis, badge 'Kurang Baik', alert bahaya |
| Success (Green)  | #1E8449        | Status 'Sangat Baik', badge sukses, konfirmasi      |
| Warning (Amber)  | #D4AC0D        | Status 'Kurang Baik', peringatan, badge pending     |
| Alert (Orange)   | #E74C1C        | Badge alert penurunan IKM di dashboard              |
| Background Admin | #F8F9FA        | Latar halaman admin                                 |
| Landing Page BG  | #EEF4FB        | Latar halaman landing page publik                   |
| Card             | #FFFFFF        | Latar kartu konten                                  |
| Text Utama       | #1F1F1F        | Teks konten, label form                             |
| Font             | Inter / Roboto | Font utama - load dari Google Fonts atau lokal      |

# **13\. RENCANA PENGUJIAN**

## **13.1 Strategi Pengujian**

| **Jenis Uji**         | **Tools**                       | **Cakupan**                                                                        |
| --------------------- | ------------------------------- | ---------------------------------------------------------------------------------- |
| Unit Testing          | PHPUnit 10.x                    | Model, helper, library kalkulasi IKM, delta IKM, QR Code, queue jobs               |
| Integration Testing   | PHPUnit + CI4 Testing           | Controller, route, interaksi model-database, backup/restore, queue worker          |
| Functional Testing    | Manual / Selenium               | Setiap use case berdasarkan SRS ini (UC-01 s.d. UC-26)                             |
| UI/UX Testing         | Manual (multi-device)           | Responsivitas, kemudahan penggunaan, aksesibilitas WCAG 2.1 AA                     |
| Performance Testing   | Apache JMeter                   | Load test 500 user, landing page load time, queue throughput 10.000 jobs           |
| Security Testing      | OWASP ZAP, Burp Suite           | SQL Injection, XSS, CSRF, broken auth, API security, penetration test pihak ketiga |
| Accessibility Testing | axe-core + manual screen reader | WCAG 2.1 Level AA untuk landing page dan survei publik                             |
| UAT                   | Tim QA + User Perwakilan        | Penerimaan keseluruhan fitur oleh pengguna nyata minimal 5 unit                    |

## **13.2 Skenario Uji Kritis**

| **TC-ID** | **Skenario**                                     | **Input**                                              | **Expected Output**                                                   |
| --------- | ------------------------------------------------ | ------------------------------------------------------ | --------------------------------------------------------------------- |
| TC-01     | Kalkulasi IKM dengan 100 responden semua nilai 4 | 100 jawaban, semua unsur nilai=4                       | IKM = 100, Mutu A, Sangat Baik                                        |
| TC-02     | Kalkulasi IKM campuran nilai 1-4                 | Jawaban acak 1-4                                       | NRR dan IKM dihitung sesuai formula                                   |
| TC-03     | Submit survei saat periode tidak aktif           | ID periode status='selesai'                            | Error 403, pesan 'Periode Tidak Aktif'                                |
| TC-04     | Login dengan kredensial salah 5x                 | Password salah berulang                                | Akun terkunci 15 menit, notif admin                                   |
| TC-05     | SQL Injection pada form login                    | ' OR '1'='1 pada username                              | Query ditolak, tidak bypass auth                                      |
| TC-06     | Ekspor PDF laporan 1000 responden                | id_periode dengan 1000 data                            | PDF tergenerate < 10 detik via queue                                  |
| TC-07     | Akses halaman admin tanpa login                  | GET /admin/dashboard tanpa session                     | Redirect ke halaman login                                             |
| TC-08     | DataTables server-side 10.000 record             | Query dengan 10.000 baris                              | Halaman load < 3 detik                                                |
| TC-09     | Responsivitas di layar 320px                     | Browser width 320px                                    | Semua elemen terlihat, tidak overflow                                 |
| TC-10     | Duplikasi pengisian survei                       | Submit 2x dari IP dan token sama                       | Tolak submission ke-2                                                 |
| TC-11     | Landing page tanpa periode aktif                 | Tidak ada periode aktif                                | Pesan 'Tidak ada survei aktif saat ini'                               |
| TC-12     | Generate QR Code unit layanan                    | id_unit valid, ukuran M, format SVG                    | QR Code tergenerate SVG, dapat dipindai, UTM tracking aktif           |
| TC-13     | Stored XSS pada form saran                       | Isi saran berisi &lt;script&gt;alert(1)&lt;/script&gt; | Teks disanitasi, script tidak dieksekusi                              |
| TC-14     | Alert penurunan IKM                              | IKM periode baru turun 6 poin dari sebelumnya          | Badge alert muncul di dashboard; notifikasi dibuat; WhatsApp terkirim |
| TC-15     | Backup manual database                           | Klik 'Mulai Backup' oleh Super Admin                   | File backup terbuat, dicatat di tb_backup_log, dapat diunduh          |
| TC-16     | Preview kuesioner tidak menyimpan data           | Isi semua unsur pada mode preview, klik kirim          | Tidak ada record baru di tb_survei_jawaban                            |
| TC-17     | Dashboard publik hanya tampil periode published  | Akses /publik/ikm tanpa login                          | Hanya periode bertanda is_published=1 yang tampil                     |
| TC-18     | Consent opt-in eksplisit UU PDP \[BARU\]         | Submit survei tanpa centang checkbox consent           | Tombol kirim disabled; tidak ada data tersimpan                       |
| TC-19     | Queue worker retry failed job \[BARU\]           | Job ikm-calculation gagal 3x                           | Retry otomatis hingga 5x; masuk dead letter queue jika tetap gagal    |
| TC-20     | REST API rate limiting \[BARU\]                  | 101 request/menit dari satu IP                         | Request ke-101 mendapat HTTP 429 Too Many Requests                    |
| TC-21     | CI/CD deployment pipeline \[BARU\]               | Push ke branch main                                    | Pipeline berjalan; deployment ke production dalam < 10 menit          |
| TC-22     | QR Code SVG dengan logo center \[BARU\]          | Generate QR Code format SVG dengan logo instansi       | Logo tampil di tengah QR Code; masih dapat dipindai                   |

# **14\. ESTIMASI WAKTU PENGEMBANGAN v2.0.0**

| **No** | **Fase / Aktivitas**                                                  | **Durasi** | **Deliverable**                                               |
| ------ | --------------------------------------------------------------------- | ---------- | ------------------------------------------------------------- |
| 1      | Analisis Kebutuhan & Penyusunan SRS v2.0.0                            | 2 Minggu   | Dokumen SRS v2.0.0                                            |
| 2      | Desain Sistem: Arsitektur, ERD, UI/UX, API Spec                       | 3 Minggu   | Dokumen Desain Teknis, Wireframe, OpenAPI 3.0 Spec            |
| 3      | Setup Infrastruktur: Docker, CI/CD, Monitoring                        | 2 Minggu   | Docker Compose, GitHub Actions, Prometheus/Grafana            |
| 4      | Database Design & Partitioning Implementation                         | 1 Minggu   | Schema v2.0.0, migration scripts, seed data                   |
| 5      | Pengembangan Core CI4: Autentikasi (OAuth/SAML/JWT), RBAC, Middleware | 3 Minggu   | Modul auth modern, API gateway, rate limiting                 |
| 6      | Pengembangan Modul Konfigurasi Survei + Queue System                  | 2 Minggu   | Unit layanan, periode, kuesioner, preview, Redis queue        |
| 7      | Pengembangan Landing Page & API Publik + Multi-Bahasa                 | 2 Minggu   | REST API, landing page responsif, database-driven translation |
| 8      | Pengembangan Survei Publik dengan Privacy/Consent (UU PDP)            | 2 Minggu   | Form survei, consent management, fingerprinting, WCAG 2.1 AA  |
| 9      | Pengembangan QR Code Modern (Endroid) dengan Tracking                 | 1 Minggu   | QR Code SVG/PNG/PDF, UTM tracking, analytics                  |
| 10     | Pengembangan Kalkulasi IKM Queue-Based + Analisis Gap                 | 2 Minggu   | Queue workers, delta IKM, alert system, caching               |
| 11     | Pengembangan Manajemen Saran & Pengaduan + Workflow                   | 1 Minggu   | Tindak lanjut workflow, status tracking, ekspor               |
| 12     | Pengembangan Notifikasi Multi-Channel (Email + WhatsApp)              | 2 Minggu   | Queue-based email, WhatsApp Business API integration          |
| 13     | Pengembangan Laporan PDF, Excel, Dashboard Publik SEO                 | 2 Minggu   | DomPDF 3.x, PhpSpreadsheet 2.x, JSON-LD, sitemap              |
| 14     | Pengembangan Backup/Restore Robust (XtraBackup, S3, Encrypt)          | 2 Minggu   | Hot backup, offsite storage, encryption, restore UI           |
| 15     | Security Hardening: Penetration Test, OWASP ZAP, CSP                  | 1.5 Minggu | Security report, remediation, CSP deployment                  |
| 16     | Performance Tuning: Load Test, Query Optimization, Cache              | 1.5 Minggu | JMeter report, Redis optimization, index tuning               |
| 17     | UAT, Bug Fix, Documentation, Training                                 | 2.5 Minggu | Sign-off UAT, user manual, admin guide, DPO guide             |
| 18     | Production Deployment with Blue-Green Strategy                        | 1 Minggu   | Live system, monitoring, rollback procedure                   |
|        | TOTAL                                                                 | 33 Minggu  | Aplikasi IKM v2.0.0 siap produksi                             |

# **15\. TIM PENGEMBANG v2.0.0**

| **Peran**                             | **Jumlah** | **Tanggung Jawab Utama**                                                             |
| ------------------------------------- | ---------- | ------------------------------------------------------------------------------------ |
| Project Manager                       | 1 orang    | Koordinasi, monitoring progress, komunikasi stakeholder, compliance tracking         |
| System Analyst / Privacy Consultant   | 1 orang    | Analisis kebutuhan, UU PDP compliance design, DPO liaison, UAT                       |
| Backend Lead (CI4/PHP 8.2)            | 1 orang    | Arsitektur backend, API design, queue system, security implementation                |
| Backend Developer (CI4/PHP 8.2)       | 2 orang    | Development backend, kalkulasi IKM, QR Code, backup/restore, SSO/OAuth/SAML          |
| Frontend Developer (Bootstrap/JS/CI4) | 1 orang    | UI Bootstrap, landing page, dashboard publik, multi-bahasa, Chart.js, Vite           |
| DevOps Engineer                       | 1 orang    | Docker, Kubernetes, CI/CD, monitoring, infrastructure as code                        |
| Database Administrator                | 1 orang    | Desain schema v2.0.0, partitioning, query optimization, backup strategy, replication |
| UI/UX Designer                        | 1 orang    | Wireframe, design system, WCAG 2.1 AA compliance, mobile-first design                |
| QA/Security Tester                    | 1 orang    | Test case, penetration testing, OWASP ZAP, load testing, accessibility testing       |
| Total                                 | 9 orang    | Penambahan DevOps Engineer dan Privacy Consultant dari v1.1.0 (7 orang)              |

# **16\. KRITERIA PENERIMAAN PROYEK v2.0.0**

Proyek dianggap selesai apabila memenuhi seluruh kriteria berikut:

- \[ \] Seluruh kebutuhan fungsional F-01 s.d. F-26 telah diimplementasikan dan lulus pengujian fungsional
- \[ \] Seluruh kebutuhan non-fungsional NF-01 s.d. NF-25 terpenuhi berdasarkan hasil pengujian
- \[ \] Hasil kalkulasi IKM dan delta IKM telah diverifikasi pada minimal 5 skenario uji berbeda
- \[ \] Queue-based processing telah diuji dengan 10.000 jobs concurrent tanpa loss
- \[ \] Landing page dan dashboard publik lulus pengujian aksesibilitas WCAG 2.1 Level AA (axe-core automated + manual screen reader)
- \[ \] QR Code yang digenerate dapat dipindai dengan benar oleh minimal 5 perangkat uji berbeda (termasuk low-end Android)
- \[ \] Fitur backup berhasil dan restore dapat diselesaikan dalam < 15 menit untuk database 5GB (RTO target)
- \[ \] Penetration testing oleh pihak ketiga dengan hasil: tidak ada vulnerability Critical atau High yang belum remediasi
- \[ \] UU PDP Compliance Assessment oleh DPO dengan hasil: tidak ada gap signifikan
- \[ \] UAT telah dilaksanakan dan ditandatangani oleh perwakilan pengguna minimal 5 unit (dari 3)
- \[ \] Dokumentasi teknis lengkap tersedia: panduan instalasi (Docker), panduan pengguna, panduan DPO, API documentation (OpenAPI 3.0), kamus data, runbook operasional
- \[ \] CI/CD pipeline berfungsi: dari push ke production deployment < 10 menit dengan rollback < 5 menit

# **17\. RINGKASAN PERUBAHAN v1.1.0 → v2.0.0**

| **Kategori** | **v1.1.0**              | **v2.0.0**                                | **Impact**                                     |
| ------------ | ----------------------- | ----------------------------------------- | ---------------------------------------------- |
| Framework    | CodeIgniter 3 (EOL)     | CodeIgniter 4.4+ (Active)                 | Security, maintainability, modern architecture |
| PHP Version  | 7.4+ (EOL)              | 8.2+ (Active, JIT)                        | Performance, security, language features       |
| Queue System | Tidak ada               | Redis + CI4 Queue                         | Scalability, async processing, reliability     |
| Database     | Flat tables             | Partitioned (RANGE)                       | Performance 10x data growth, archival          |
| Backup       | mysqldump manual        | Percona XtraBackup hot + S3 offsite       | RPO 6 jam, RTO 15 menit, business continuity   |
| QR Code      | PhpQRCode 1.1 (stagnan) | Endroid QR Code 5.x                       | SVG, logo center, styling, analytics           |
| Notifikasi   | Email + in-app          | \+ WhatsApp Business API                  | Engagement, response rate, UX                  |
| Autentikasi  | LDAP only (opsional)    | OAuth 2.0, OpenID Connect, SAML 2.0, LDAP | Modern identity, SSO integration               |
| API          | Tidak ada               | REST API first, OpenAPI 3.0               | Mobile app ready, third-party integration      |
| Privacy      | Tidak disebut           | UU PDP full compliance                    | Legal requirement, trust, accountability       |
| DevOps       | Tidak ada               | Docker, CI/CD, Kubernetes ready           | Deployment speed, consistency, scalability     |
| Monitoring   | Tidak ada               | Prometheus, Grafana, structured logging   | Observability, proactive alerting              |
| Security     | Basic (bcrypt, CSRF)    | CSP strict, JWT RS256, MFA, HSM, WORM     | Defense in depth, compliance                   |
| Team Size    | 7 orang                 | 9 orang (+DevOps, +Privacy Consultant)    | Capability coverage                            |
| Timeline     | 22 minggu               | 33 minggu                                 | +50% untuk quality dan compliance              |

# **LAMPIRAN**

## **Lampiran A - Contoh Kuesioner IKM**

Contoh tampilan kuesioner IKM yang disajikan kepada responden, mengacu pada 9 unsur wajib PermenPANRB 14/2017:

| **No** | **Pertanyaan**                                                            | **Tidak Baik (1)** | **Kurang Baik (2)** | **Baik (3)** | **Sangat Baik (4)** |
| ------ | ------------------------------------------------------------------------- | ------------------ | ------------------- | ------------ | ------------------- |
| 1      | Bagaimana pendapat Saudara tentang kesesuaian persyaratan pelayanan?      | ○                  | ○                   | ○            | ○                   |
| 2      | Bagaimana pendapat Saudara tentang kemudahan prosedur pelayanan?          | ○                  | ○                   | ○            | ○                   |
| 3      | Bagaimana pendapat Saudara tentang kecepatan waktu pelayanan?             | ○                  | ○                   | ○            | ○                   |
| 4      | Bagaimana pendapat Saudara tentang kewajaran biaya/tarif pelayanan?       | ○                  | ○                   | ○            | ○                   |
| 5      | Bagaimana pendapat Saudara tentang kesesuaian produk pelayanan?           | ○                  | ○                   | ○            | ○                   |
| 6      | Bagaimana pendapat Saudara tentang kompetensi petugas pelayanan?          | ○                  | ○                   | ○            | ○                   |
| 7      | Bagaimana pendapat Saudara tentang perilaku (sopan-santun) petugas?       | ○                  | ○                   | ○            | ○                   |
| 8      | Bagaimana pendapat Saudara tentang kualitas sarana dan prasarana?         | ○                  | ○                   | ○            | ○                   |
| 9      | Bagaimana pendapat Saudara tentang penanganan pengaduan pengguna layanan? | ○                  | ○                   | ○            | ○                   |

## **Lampiran B - Ringkasan Perubahan v1.0.0 → v1.1.0**

| **Kategori**   | **Item**                   | **Deskripsi Perubahan**                                             |
| -------------- | -------------------------- | ------------------------------------------------------------------- |
| Fitur Baru     | F-13 Landing Page          | Halaman homepage publik sebagai pintu masuk responden               |
| Fitur Baru     | F-14 QR Code Generator     | Generate, preview, unduh, dan cetak QR Code per survei              |
| Fitur Baru     | F-15 Notifikasi & Reminder | Notifikasi otomatis periode hampir berakhir & target belum tercapai |
| Fitur Baru     | F-16 Manajemen Saran       | Kelola saran/pengaduan responden dengan tindak lanjut               |
| Fitur Baru     | F-17 Analisis Gap & Alert  | Deteksi penurunan IKM antar periode dengan alert otomatis           |
| Fitur Baru     | F-18 Multi-Bahasa          | Dukungan bahasa daerah pada antarmuka publik                        |
| Fitur Baru     | F-19 Preview Kuesioner     | Pratinjau kuesioner dari sudut pandang responden                    |
| Fitur Baru     | F-20 Dashboard Publik      | Halaman transparansi IKM yang dapat diakses publik                  |
| Fitur Baru     | F-21 Backup & Restore      | Backup terjadwal dan manual dengan kemampuan restore                |
| Fitur Baru     | F-22 Integrasi SSO/LDAP    | Opsi autentikasi terpusat untuk pengguna internal (opsional)        |
| Perluasan DB   | tb_saran                   | Tabel baru untuk manajemen saran/pengaduan responden                |
| Perluasan DB   | tb_notifikasi              | Tabel baru untuk notifikasi dan reminder sistem                     |
| Perluasan DB   | tb_backup_log              | Tabel baru untuk riwayat backup database                            |
| Perluasan DB   | tb_rekap_ikm               | Penambahan kolom delta_ikm, flag_alert, is_published, published_at  |
| Estimasi Waktu | Total Durasi               | Bertambah dari 17 minggu menjadi 22 minggu                          |
| Tim            | Tidak berubah              | Komposisi tim tetap 7 peran; beban kerja backend meningkat          |

## **Lampiran C - Riwayat Versi Dokumen**

Dokumen ini dikelola dengan sistem version control. Setiap perubahan signifikan terhadap kebutuhan sistem harus didokumentasikan di tabel riwayat revisi dan disetujui oleh Project Manager serta perwakilan instansi pemerintah terkait.

Untuk versi terkini peraturan yang menjadi dasar hukum, silakan merujuk ke Jdih.menpan.go.id dan peraturan.go.id.