# Aplikasi Survei Kepuasan Masyarakat (IKM) v2.0.0

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![CodeIgniter 4.4+](https://img.shields.io/badge/CodeIgniter-4.4+-red.svg)](https://codeigniter.com/)

Aplikasi Survei Kepuasan Masyarakat (IKM) untuk instansi pemerintah sesuai **PermenPANRB No. 14 Tahun 2017**.

## 📋 Fitur Utama

### Core Features
- ✅ Pengukuran Indeks Kepuasan Masyarakat sesuai PermenPANRB 14/2017
- ✅ Multi-survey dengan berbagai elemen dan pertanyaan
- ✅ Real-time analytics dan dashboard
- ✅ Export laporan (PDF, Excel, CSV)
- ✅ Responsive design (Bootstrap 5.3)

### Security & Compliance
- 🔐 Autentikasi modern (OAuth2/SAML/OIDC)
- 🔐 Multi-Factor Authentication (MFA/TOTP)
- 🔐 Role-Based Access Control (RBAC)
- 🔐 UU PDP Compliance dengan Consent Management
- 🔐 Audit logging lengkap
- 🔐 CSRF Protection & Rate Limiting

### Architecture
- 🏗️ Monolitik siap-microservices
- 🏗️ Queue system dengan Redis
- 🏗️ Database partitioning ready
- 🏗️ Docker & Kubernetes ready
- 🏗️ CI/CD pipeline (GitHub Actions)

### Monitoring & Observability
- 📊 Prometheus metrics
- 📊 Grafana dashboards
- 📊 Health checks
- 📊 Performance monitoring

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.2+, CodeIgniter 4.4.x |
| Frontend (Admin) | Bootstrap 5.3, jQuery 3.7, Chart.js 4 |
| Frontend (Publik) | Bootstrap 5.3, Vanilla JS |
| Database | MySQL 8.0 dengan partitioning |
| Cache/Queue | Redis 7.x |
| Container | Docker, Docker Compose |
| Orchestration | Kubernetes-ready |
| Monitoring | Prometheus, Grafana |
| CI/CD | GitHub Actions |

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- PHP 8.2+ (untuk local development tanpa Docker)
- Composer
- MySQL 8.0+
- Redis 7.x

### Installation dengan Docker

```bash
# Clone repository
git clone https://github.com/your-org/ikm-app.git
cd ikm-app

# Copy environment file
cp .env.example .env

# Generate encryption key
php spark key:generate --show

# Update .env dengan encryption key
# ENCRYPTION_KEY=your-generated-key-here

# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php spark migrate --all

# Seed initial data (optional)
docker-compose exec app php spark db:seed

# Access application
# Web: http://localhost:8080
# Prometheus: http://localhost:9090
# Grafana: http://localhost:3000
```

### Local Development (Tanpa Docker)

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate encryption key
php spark key:generate

# Setup database
mysql -u root -p -e "CREATE DATABASE ikm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php spark migrate

# Seed data
php spark db:seed

# Start development server
php spark serve

# Access: http://localhost:8080
```

## 📁 Struktur Direktori

```
ikm-app/
├── app/
│   ├── Config/           # Konfigurasi aplikasi
│   ├── Controllers/      # Request handlers
│   │   ├── Admin/        # Admin controllers
│   │   ├── Api/          # API controllers
│   │   ├── Auth/         # Authentication controllers
│   │   └── Publik/       # Public survey controllers
│   ├── Database/
│   │   ├── Migrations/   # Database migrations
│   │   └── Seeds/        # Database seeds
│   ├── Entities/         # Domain entities
│   ├── Filters/          # Middleware filters
│   ├── Helpers/          # Helper functions
│   ├── Language/         # Translations
│   ├── Libraries/        # Custom libraries
│   ├── Models/           # Database models
│   ├── Queue/
│   │   └── Jobs/         # Queue jobs
│   ├── Services/         # Service layer
│   └── Views/            # View templates
├── docker/               # Docker configuration
├── public/               # Public assets
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── images/
│       └── fonts/
├── tests/                # PHPUnit tests
├── writable/             # Writable directories
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/
└── .github/
    └── workflows/        # CI/CD pipelines
```

## 🔧 Configuration

### Environment Variables

Edit `.env` file untuk konfigurasi:

```env
# Database
database.default.hostname = localhost
database.default.database = ikm_db
database.default.username = root
database.default.password = your_password

# Redis
redis.default.hostname = 127.0.0.1
redis.default.port = 6379

# OAuth2
oauth2.enabled = true
oauth2.provider = google
oauth2.clientId = your-client-id
oauth2.clientSecret = your-client-secret

# MFA
mfa.enabled = true
mfa.requiredForRoles = admin,super_admin

# UU PDP
pdp.consentEnabled = true
pdp.dataRetentionDays = 730
```

## 📊 API Documentation

API endpoints tersedia di `/api/v1/`. Dokumentasi lengkap tersedia setelah instalasi.

### Authentication

```bash
# Get API token
curl -X POST http://localhost:8080/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# Use token in requests
curl http://localhost:8080/api/v1/surveys \
  -H "Authorization: Bearer {token}"
```

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test
./vendor/bin/phpunit tests/unit/SurveyTest.php
```

## 📈 Monitoring

### Prometheus Metrics

Akses metrics di `http://localhost:9090/metrics`:
- Request rate
- Response time
- Error rate
- Queue length
- Database connections

### Grafana Dashboards

Pre-configured dashboards di `http://localhost:3000`:
- Application Overview
- Survey Analytics
- System Performance
- Queue Monitoring

## 🔒 Security

### Default Credentials (Development Only)

```
Username: admin
Password: admin123
```

**⚠️ Penting:** Ganti password default sebelum production!

### Security Best Practices

1. Selalu gunakan HTTPS di production
2. Rotate encryption keys secara berkala
3. Enable MFA untuk semua admin users
4. Review audit logs secara rutin
5. Backup database secara teratur

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 👥 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📞 Support

Untuk bantuan teknis:
- Email: support@ikm.go.id
- Documentation: https://docs.ikm.go.id

---

**Developed with ❤️ for Indonesian Government**
