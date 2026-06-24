# =============================================================================
# Docker Configuration untuk Aplikasi IKM v2.0.0
# =============================================================================

## 📋 Ringkasan Konfigurasi Docker

Dokumen ini menjelaskan konfigurasi Docker lengkap untuk development environment Aplikasi IKM v2.0.0.

## 🏗️ Arsitektur Services

```
┌─────────────────────────────────────────────────────────────────┐
│                    IKM Application Stack                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐   │
│  │  web-service │────▶│  app-service │────▶│  db-service  │   │
│  │  (Nginx)     │     │  (PHP-FPM)   │     │   (MySQL)    │   │
│  │  Port: 80    │     │  Port: 8080  │     │  Port: 3306  │   │
│  └──────────────┘     └──────┬───────┘     └──────────────┘   │
│                              │                                 │
│                              ▼                                 │
│                       ┌──────────────┐                        │
│                       │redis-service │                        │
│                       │   (Redis 7)  │                        │
│                       │  Port: 6379  │                        │
│                       └──────────────┘                        │
│                              │                                 │
│         ┌────────────────────┼────────────────────┐           │
│         ▼                    ▼                    ▼           │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    │
│  │queue-worker  │    │  scheduler   │    │  prometheus  │    │
│  │  (Scale: 2)  │    │   (Cron)     │    │  (Metrics)   │    │
│  └──────────────┘    └──────────────┘    └──────┬───────┘    │
│                                                  │            │
│                                                  ▼            │
│                                           ┌──────────────┐   │
│                                           │   grafana    │   │
│                                           │ (Dashboard)  │   │
│                                           │  Port: 3000  │   │
│                                           └──────────────┘   │
│                                                                │
└─────────────────────────────────────────────────────────────────┘
```

## 🚀 Services yang Tersedia

### 1. **app-service** (PHP 8.2-FPM + Nginx)
- **Image**: Custom build dari `php:8.2-fpm-alpine`
- **Ports**: 8080 (HTTP), 8443 (HTTPS), 9003 (Xdebug)
- **Features**:
  - Volume mount untuk hot reload development
  - Xdebug enabled untuk debugging
  - Nginx sebagai reverse proxy internal
  - Health check endpoint `/health`
  - Dependency: db-service, redis-service

### 2. **web-service** (Nginx Reverse Proxy - Optional)
- **Image**: `nginx:1.24-alpine`
- **Ports**: 80 (HTTP), 443 (HTTPS)
- **Features**:
  - Reverse proxy ke app-service
  - Static file serving
  - SSL termination (jika dikonfigurasi)

### 3. **db-service** (MySQL 8.0)
- **Image**: `mysql:8.0`
- **Port**: 3306
- **Features**:
  - Partitioning support enabled
  - Auto-initialization dengan script SQL
  - Persistent volume untuk data
  - Optimized untuk workload IKM
  - Health check dengan mysqladmin

### 4. **redis-service** (Redis 7.x)
- **Image**: `redis:7-alpine`
- **Port**: 6379
- **Features**:
  - Cache, Queue, dan Session storage
  - AOF persistence enabled
  - Memory limit: 256MB
  - LRU eviction policy
  - Health check dengan redis-cli ping

### 5. **queue-worker** (Background Jobs)
- **Image**: Custom build (sama dengan app-service)
- **Replicas**: 2 (scalable)
- **Features**:
  - Process queue jobs dari Redis
  - Auto-restart on failure
  - Resource limits configured
  - Health check dengan pgrep

### 6. **scheduler** (Cron Jobs)
- **Image**: Custom build (sama dengan app-service)
- **Features**:
  - Menjalankan scheduled tasks setiap menit
  - Alternative to system cron
  - Integrated dengan CodeIgniter scheduler

### 7. **prometheus** (Monitoring & Alerting)
- **Image**: `prom/prometheus:v2.47.0`
- **Port**: 9090
- **Features**:
  - Metrics collection setiap 15s
  - Alert rules untuk semua services
  - Data retention: 30 days
  - Pre-configured scrape configs

### 8. **grafana** (Dashboard Visualization)
- **Image**: `grafana/grafana:10.1.0`
- **Port**: 3000
- **Credentials**: admin / admin123
- **Features**:
  - Auto-provisioned datasources
  - Pre-configured dashboards
  - Alert visualization
  - Custom plugins installed

## 📁 Struktur Direktori

```
docker/
├── Dockerfile                      # PHP 8.2 + Nginx + tools
├── docker-compose.yml              # Orchestration semua services
├── .env.example                    # Template environment variables
├── nginx.conf                      # Nginx configuration
├── php.ini                         # PHP optimization settings
├── supervisord.conf                # Process manager config
├── entrypoint.sh                   # Container startup script
├── prometheus.yml                  # Prometheus scrape config
├── init-scripts/
│   └── 01_init_database.sql       # Database initialization
├── prometheus-rules/
│   └── ikm_alerts.yml             # Alert rules definitions
└── grafana/
    ├── provisioning/
    │   ├── datasources/
    │   │   └── datasources.yml    # Datasource auto-config
    │   └── dashboards/
    │       └── dashboards.yml     # Dashboard auto-provision
    └── dashboards/                 # Custom dashboard JSON files
```

## 🔧 Quick Start Commands

### Development Setup
```bash
# Copy environment file
cp docker/.env.example .env

# Generate APP_KEY (untuk encryption)
docker run --rm php:8.2-cli php -r "echo 'APP_KEY='.base64_encode(random_bytes(32)).PHP_EOL;" >> .env

# Build dan start semua services
docker-compose up -d --build

# Lihat logs semua services
docker-compose logs -f

# Lihat status health semua containers
docker-compose ps
```

### Development Workflow
```bash
# Restart app-service setelah perubahan code
docker-compose restart app-service

# Akses container app-service
docker-compose exec app-service sh

# Jalankan migration
docker-compose exec app-service php spark migrate

# Jalankan seeder
docker-compose exec app-service php spark db:seed

# Clear cache
docker-compose exec app-service php spark cache:clear

# Tail logs aplikasi
docker-compose exec app-service tail -f writable/logs/log-*.php
```

### Monitoring Access
```
Grafana Dashboard: http://localhost:3000
  Username: admin
  Password: admin123

Prometheus Metrics: http://localhost:9090

Application: http://localhost:8080
```

### Database Access
```bash
# MySQL CLI
docker-compose exec db-service mysql -u root -p

# Atau dari host
mysql -h 127.0.0.1 -P 3306 -u root -p

# Redis CLI
docker-compose exec redis-service redis-cli
```

### Queue Management
```bash
# Lihat jumlah worker aktif
docker-compose ps queue-worker

# Scale worker menjadi 4 replicas
docker-compose up -d --scale queue-worker=4

# Monitor queue length
docker-compose exec redis-service redis-cli llen queue:default
```

## 🔐 Security Considerations

### Development vs Production

| Setting | Development | Production |
|---------|-------------|------------|
| APP_DEBUG | true | false |
| XDEBUG | enabled | disabled |
| DB_PASSWORD | secret123 | Strong random password |
| REDIS_PASSWORD | (empty) | Strong password |
| Exposed Ports | Multiple | Only 80/443 |
| Volume Mounts | Source code | Read-only configs |

### Production Checklist
- [ ] Ganti semua default passwords
- [ ] Disable Xdebug
- [ ] Set APP_DEBUG=false
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up backup strategy
- [ ] Configure log rotation
- [ ] Enable monitoring alerts
- [ ] Set resource limits
- [ ] Configure network policies

## 🛠️ Troubleshooting

### Common Issues

#### 1. Container tidak start
```bash
# Cek logs
docker-compose logs <service-name>

# Cek resource usage
docker stats

# Restart services
docker-compose down && docker-compose up -d
```

#### 2. Database connection failed
```bash
# Pastikan db-service healthy
docker-compose ps db-service

# Cek koneksi dari app-service
docker-compose exec app-service mysql -h db-service -u root -p

# Restart database
docker-compose restart db-service
```

#### 3. Queue worker tidak process jobs
```bash
# Cek Redis connection
docker-compose exec redis-service redis-cli ping

# Lihat queue length
docker-compose exec redis-service redis-cli llen queue:default

# Restart workers
docker-compose restart queue-worker
```

#### 4. Xdebug tidak connect
```bash
# Pastikan port 9003 terbuka
docker-compose ps app-service

# Cek Xdebug configuration
docker-compose exec app-service php -i | grep xdebug

# Test connection dari IDE
# IDE Key harus sama: VSCODE (default)
```

## 📊 Monitoring & Alerts

### Metrics Collected
- Application response time
- Request rate dan error rate
- Database connections dan query performance
- Redis memory usage dan queue length
- Queue job processing rate
- System resources (CPU, memory, disk)

### Alert Thresholds
- **Critical**: Service down > 1 minute
- **Warning**: Error rate > 5%, Response time > 2s
- **Info**: Consent expiry, Data retention

### Dashboard Panels
1. **Application Overview**: Request rate, response time, errors
2. **Database Health**: Connections, queries, slow queries
3. **Queue Status**: Jobs processed, failed, pending
4. **System Resources**: CPU, memory, disk usage
5. **PDP Compliance**: Consents, audit logs, data retention

## 🚀 Scaling Strategy

### Horizontal Scaling
```bash
# Scale queue workers
docker-compose up -d --scale queue-worker=5

# Scale app-service (requires load balancer)
docker-compose up -d --scale app-service=3
```

### Resource Limits
Set di `docker-compose.yml`:
```yaml
deploy:
  resources:
    limits:
      cpus: '0.5'
      memory: 256M
    reservations:
      cpus: '0.25'
      memory: 128M
```

### Kubernetes Migration
Untuk production, gunakan Kubernetes:
- Deploy manifests tersedia di `k8s/` directory
- Helm chart available
- Auto-scaling dengan HPA
- Service mesh ready (Istio compatible)

## 📝 Next Steps

1. ✅ Docker configuration complete
2. ⏭️ Database migrations & models
3. ⏭️ Authentication module (OAuth2/SAML/OIDC)
4. ⏭️ Survey management module
5. ⏭️ Queue jobs implementation
6. ⏭️ Monitoring dashboards setup

---

**Version**: 2.0.0  
**Last Updated**: 2024  
**Maintainer**: IKM Development Team
