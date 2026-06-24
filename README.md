# Aplikasi IKM v2.0.0 - Indeks Kepuasan Masyarakat

## 📋 Informasi Umum

**Aplikasi IKM (Indeks Kepuasan Masyarakat) v2.0.0** adalah sistem informasi berbasis web yang dibangun dengan **CodeIgniter 4** untuk mengukur tingkat kepuasan masyarakat terhadap kualitas pelayanan publik. Aplikasi ini dirancang sesuai dengan standar peraturan pemerintah Indonesia tentang pengukuran IKM dan implementasi prinsip-prinsip Good Governance.

Sistem ini mendukung pengelolaan survei kepuasan secara terintegrasi, mulai dari manajemen periode survei, kuesioner berbasis 9 unsur IKM, pengumpulan respons, kalkulasi nilai IKM otomatis, hingga pelaporan dan analytics yang komprehensif.

### 🔗 Teknologi Utama
- **Framework**: CodeIgniter 4.x
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, Chart.js, DataTables
- **Authentication**: JWT RS256, MFA TOTP, OAuth 2.0
- **API**: RESTful API dengan dokumentasi lengkap
- **Security**: CSRF Protection, Input Validation, Role-Based Access Control (RBAC)

---

## ✨ Fitur Aplikasi

### 1. **Manajemen Survei & Periode**
- ✅ Pengelolaan periode survei aktif/non-aktif
- ✅ Penjadwalan otomatis periode survei
- ✅ Multi-periode untuk perbandingan temporal
- ✅ Preview kuesioner sebelum publikasi

### 2. **Kuesioner IKM (9 Unsur)**
- ✅ Manajemen 9 unsur IKM standar:
  1. Persyaratan Pelayanan
  2. Sistem, Mekanisme, dan Prosedur
  3. Waktu Pelayanan
  4. Biaya/Tarif
  5. Produk Spesifikasi Jenis Pelayanan
  6. Kompetensi Pelaksana
  7. Perilaku Pelaksana
  8. Penanganan Pengaduan, Saran, dan Masukan
  9. Sarana dan Prasarana
- ✅ Customizable pertanyaan per unsur
- ✅ Berbagai tipe input (radio, checkbox, scale, textarea)
- ✅ Validasi real-time pada form survei

### 3. **Pengumpulan Respons**
- ✅ Form survei publik online
- ✅ Anonimitas responden (opsional)
- ✅ Tracking respondent dengan token unik
- ✅ Input manual oleh operator
- ✅ Dukungan multi-device (responsive)
- ✅ Captcha anti-bot

### 4. **Kalkulasi & Analytics IKM**
- ✅ Perhitungan nilai IKM otomatis berdasarkan formula BKN
- ✅ Konversi nilai mentah ke skala 0-100
- ✅ Klasifikasi mutu pelayanan (A-E)
- ✅ Dashboard analytics dengan visualisasi chart
- ✅ Perbandingan IKM per unit layanan
- ✅ Trend analysis antar periode
- ✅ Export laporan (PDF, Excel, CSV)

### 5. **Manajemen Pengguna & Hak Akses**
- ✅ **Role-Based Access Control (RBAC)** dengan 6 role:
  - **Super Admin**: Full access system
  - **Admin**: Manajemen operasional
  - **Operator**: Input data & verifikasi
  - **Pimpinan**: View dashboard & laporan
  - **DPO (Data Protection Officer)**: Compliance PDP
  - **DevOps**: Maintenance & backup
- ✅ Multi-factor Authentication (MFA) wajib untuk role tertentu
- ✅ OAuth 2.0 integration (Google, GitHub, Facebook)
- ✅ Session management dengan JWT

### 6. **Kepatuhan UU PDP (Perlindungan Data Pribadi)**
- ✅ Consent management untuk pengumpulan data
- ✅ Audit log semua aktivitas sistem
- ✅ Data anonymization & pseudonymization
- ✅ Right to access, rectification, erasure
- ✅ Encryption data at-rest dan in-transit
- ✅ Privacy by design implementation

### 7. **Manajemen Unit Layanan**
- ✅ CRUD unit kerja/layanan
- ✅ Hierarki instansi (multi-level)
- ✅ Profil unit dengan kontak & jam operasi
- ✅ Assignment petugas per unit

### 8. **Monitoring & Queue System**
- ✅ Real-time monitoring antrian respons
- ✅ Worker management untuk background jobs
- ✅ Failed jobs retry mechanism
- ✅ Queue settings configuration

### 9. **Laporan & Export**
- ✅ Laporan IKM per periode
- ✅ Laporan per unit layanan
- ✅ Grafik trend IKM
- ✅ Export PDF dengan template resmi
- ✅ Export Excel untuk analisis lanjutan
- ✅ Print-friendly views

### 10. **API & Integrasi**
- ✅ RESTful API untuk mobile apps
- ✅ Webhook support untuk integrasi eksternal
- ✅ API documentation built-in
- ✅ Rate limiting & API authentication

### 11. **Fitur Tambahan**
- ✅ Manajemen saran & pengaduan
- ✅ Notifikasi email (SMTP)
- ✅ Backup & restore database
- ✅ System settings configurable
- ✅ Multi-language ready (i18n)
- ✅ Dark mode support (coming soon)

---

## 👥 Pengguna Aplikasi

| Role | Deskripsi | Akses Utama |
|------|-----------|-------------|
| **Super Admin** | Administrator tertinggi sistem | Full access: user management, roles, permissions, system settings, audit logs, backup |
| **Admin** | Pengelola operasional harian | Manajemen survei, periode, kuesioner, unit layanan, users (level operator), laporan |
| **Operator** | Petugas input & verifikasi | Input respons manual, verifikasi data, view unit sendiri, export data terbatas |
| **Pimpinan** | Eksekutif/Direktur | Dashboard analytics, laporan IKM, comparison charts, trend analysis (read-only) |
| **DPO** | Data Protection Officer | Consent management, audit logs, PDP compliance reports, data access requests |
| **DevOps** | Technical maintenance | System monitoring, queue management, backup/restore, webhook configuration, API keys |
| **Publik** | Responden survei | Akses form survei, view hasil agregat (jika dipublikasikan), submit saran |

---

## 🚀 Cara Install Aplikasi

### Prasyarat Sistem
- PHP >= 7.4 (direkomendasikan PHP 8.0+)
- MySQL >= 5.7 atau MariaDB >= 10.2
- Composer >= 2.0
- OpenSSL extension
- Intl extension
- MBString extension
- Git

### Langkah Instalasi

#### 1. Clone Repository
```bash
git clone <repository-url> ikm-app
cd ikm-app
```

#### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```
*Catatan: Gunakan `--dev` flag jika ingin menjalankan tests.*

#### 3. Setup Environment
```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi berikut:
```env
# App Configuration
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

# Database Configuration
database.default.hostname = localhost
database.default.database = ikm_db
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi

# JWT Configuration
jwt.private_key = 'keys/jwt_private.pem'
jwt.public_key = 'keys/jwt_public.pem'
jwt.expiration = 3600

# Email Configuration (SMTP)
email.from = 'noreply@yourdomain.com'
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = 'your_email@gmail.com'
email.SMTPPass = 'your_app_password'
email.SMTPPort = 587

# Security
encryption.key = 'Your32CharacterEncryptionKey!!'
```

#### 4. Generate JWT Keys
```bash
mkdir -p keys
openssl genrsa -out keys/jwt_private.pem 2048
openssl rsa -in keys/jwt_private.pem -pubout -out keys/jwt_public.pem

# Set proper permissions (Linux/Mac)
chmod 600 keys/jwt_private.pem
chmod 644 keys/jwt_public.pem
```

#### 5. Generate Application Key
```bash
php spark key:generate
```

#### 6. Setup Database
Buat database baru:
```sql
CREATE DATABASE ikm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jalankan migrations:
```bash
php spark migrate
```

*Opsional: Seed data awal untuk testing*
```bash
php spark db:seed IkmInitialDataSeeder
```

#### 7. Set Permissions
```bash
# Linux/Mac
chmod -R 755 writable/
chmod -R 755 keys/

# Windows (via PowerShell)
icacls writable /grant Everyone:(OI)(CI)F /T
icacls keys /grant Everyone:(OI)(CI)F /T
```

#### 8. Jalankan Aplikasi
```bash
php spark serve
```

Akses aplikasi di browser: **http://localhost:8080**

---

## 🔐 User Default untuk Testing

Setelah menjalankan seeder, gunakan kredensial berikut untuk login:

| Username | Password | Role | MFA Required |
|----------|----------|------|--------------|
| `superadmin` | `SuperAdmin123!` | Super Admin | ✅ Ya |
| `admin` | `Admin123!` | Admin | ❌ Tidak |
| `operator` | `Operator123!` | Operator | ❌ Tidak |
| `pimpinan` | `Pimpinan123!` | Pimpinan | ❌ Tidak |
| `dpo` | `DPO123!` | DPO | ✅ Ya |
| `devops` | `DevOps123!` | DevOps | ❌ Tidak |

**Catatan**: Untuk role yang memerlukan MFA, scan QR code yang ditampilkan saat login pertama kali menggunakan aplikasi authenticator (Google Authenticator, Authy, dll).

---

## 📁 Struktur Direktori Penting

```
/workspace
├── app/
│   ├── Controllers/          # Semua controller aplikasi
│   │   ├── Api/             # API controllers
│   │   ├── Admin/           # Admin panel controllers
│   │   └── ...              # Lainnya
│   ├── Models/              # Database models
│   ├── Views/               # Template views
│   │   ├── admin/           # Admin panel views
│   │   ├── auth/            # Authentication views
│   │   ├── survey/          # Public survey views
│   │   └── templates/       # Layout templates
│   ├── Config/              # Configuration files
│   ├── Database/
│   │   ├── Migrations/      # Database migrations
│   │   └── Seeds/           # Database seeders
│   ├── Filters/             # Request filters (auth, CORS, etc.)
│   ├── Helpers/             # Custom helper functions
│   ├── Services/            # Business logic services
│   └── Jobs/                # Queue jobs
├── public/                  # Web root (assets, uploads)
├── writable/                # Writable directory (logs, cache)
├── keys/                    # JWT keys
├── .env                     # Environment configuration
├── spark                    # CodeIgniter CLI tool
└── composer.json            # Dependency management
```

---

## 🛠️ Command Line Interface (CLI)

Aplikasi menyediakan berbagai command melalui `spark`:

```bash
# Database Operations
php spark migrate                 # Run all migrations
php spark migrate:status          # Check migration status
php spark db:seed ClassName       # Run specific seeder

# Server
php spark serve                   # Start development server
php spark serve --port 8081       # Custom port

# Cache
php spark cache:clear             # Clear all caches

# Cryptography
php spark key:generate            # Generate encryption key

# Custom Commands
php spark ikm:calculate           # Calculate IKM scores
php spark ikm:report              # Generate periodic report
php spark queue:work              # Start queue worker
```

---

## 📊 API Endpoints

### Public APIs
```
GET    /api/survey/active-period      # Get active survey period
GET    /api/survey/elements           # Get questionnaire elements
POST   /api/survey/submit             # Submit survey response
GET    /api/survey/stats/{unit_id}    # Get unit statistics
GET    /api/survey/units              # Get active service units
```

### Protected APIs (Requires Authentication)
```
GET    /api/users                     # List users
POST   /api/users                     # Create user
PUT    /api/users/{id}                # Update user
DELETE /api/users/{id}                # Delete user

GET    /api/responses                 # List responses
GET    /api/responses/{id}            # Get response detail
GET    /api/responses/stats           # Response statistics
GET    /api/responses/export          # Export as CSV

GET    /api/analytics/dashboard       # Dashboard analytics
GET    /api/analytics/element/{id}    # Element detail analytics
GET    /api/analytics/comparison      # Compare IKM
```

### Webhooks
```
POST   /api/webhooks/survey-response      # Survey response webhook
POST   /api/webhooks/payment-notification # Payment webhook
POST   /api/webhooks/whatsapp-status      # WhatsApp status webhook
```

*Dokumentasi API lengkap tersedia di `/api/docs` (jika diinstall)*

---

## 🔒 Keamanan

### Fitur Keamanan yang Diimplementasikan:
- ✅ **CSRF Protection** pada semua form
- ✅ **Input Validation** dengan aturan ketat
- ✅ **Output Escaping** untuk mencegah XSS
- ✅ **Password Hashing** menggunakan bcrypt
- ✅ **JWT Token** dengan algoritma RS256
- ✅ **Refresh Token Rotation**
- ✅ **Rate Limiting** pada API endpoints
- ✅ **SQL Injection Prevention** dengan prepared statements
- ✅ **Session Security** dengan HTTPOnly & Secure flags
- ✅ **Audit Logging** semua aktivitas kritis
- ✅ **Role-Based Access Control** granular
- ✅ **MFA** untuk role sensitif
- ✅ **Data Encryption** untuk data sensitif

---

## 🧪 Testing

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit --testsuite Feature

# Run with coverage
php vendor/bin/phpunit --coverage-html build/coverage
```

---

## 📝 License

MIT License - Lihat file [LICENSE](LICENSE) untuk detail.

---

## 🤝 Kontribusi

Kontribusi sangat diapresiasi! Silakan:
1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📞 Support

Untuk bantuan teknis atau pertanyaan:
- 📧 Email: support@yourdomain.com
- 💬 Issue Tracker: GitHub Issues
- 📖 Dokumentasi: `/docs` directory

---

**Dibangun dengan ❤️ menggunakan CodeIgniter 4**
