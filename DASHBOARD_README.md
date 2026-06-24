# Dashboard Internal IKM - Dokumentasi Implementasi

## Overview
Dashboard Internal untuk monitoring Indeks Kepuasan Masyarakat (IKM) sesuai SRS F-07 dan F-17.

## Fitur Utama

### 1. Grafik Tren IKM per Unit (Chart.js 4)
- Line chart menampilkan tren nilai IKM dari waktu ke waktu
- Filter berdasarkan unit layanan dan periode
- Data di-cache selama 1 jam menggunakan Redis

### 2. Distribusi Jawaban per Unsur
- Doughnut chart menampilkan distribusi jawaban per unsur pelayanan
- Menampilkan total jawaban dan rata-rata nilai per unsur

### 3. Rekapitulasi Periode
- Total responden
- Nilai IKM rata-rata
- Mutu pelayanan (Sangat Baik/Baik/Kurang Baik/Tidak Baik)
- Delta (perubahan vs periode sebelumnya)

### 4. Alert Penurunan IKM
- Badge merah muncul jika ada unit dengan penurunan > 2 poin
- Notifikasi visual di bagian atas dashboard

### 5. Filter
- Unit Layanan (semua unit atau spesifik)
- Tahun (range 3 tahun)
- Periode (semua atau spesifik)

### 6. Server-side Rendering dengan Cache Redis
- Cache key unik berdasarkan kombinasi filter
- TTL: 3600 detik (1 jam)
- Auto-refresh via AJAX button

## Struktur File

```
app/
├── Controllers/
│   ├── Admin/
│   │   └── DashboardController.php      # Controller utama dashboard
│   └── Api/
│       └── DashboardApiController.php   # API endpoint untuk AJAX
├── Models/
│   └── DashboardModel.php               # Query aggregated data
└── Views/
    ├── admin/
    │   └── dashboard/
    │       └── index.php                # View dashboard utama
    └── templates/
        └── admin_layout.php             # Layout admin dengan navbar

database/
└── migrations/
    └── (menggunakan tabel existing)
```

## Routes

```php
// Dashboard HTML
GET /admin/dashboard          -> DashboardController::index

// API Endpoint
GET /api/dashboard/data       -> DashboardApiController::getData
```

## Dependencies

- **Chart.js 4.x**: Untuk visualisasi grafik
- **Bootstrap 5**: Untuk styling responsive
- **Font Awesome 6**: Untuk icon
- **Redis**: Untuk caching (driver cache CodeIgniter)

## Database Tables Required

```sql
-- Tabel responses (tb_survei_responses)
- id, responden_id, unit_id, periode_id, unsur_id, nilai

-- Tabel periode (tb_periode)
- id, nama_periode, tahun, urutan

-- Tabel unit_layanan (tb_unit_layanan)
- id, nama_unit

-- Tabel unsur_pelayanan (tb_unsur_pelayanan)
- id, nama_unsur
```

## Cara Penggunaan

### 1. Akses Dashboard
```
https://your-domain.com/admin/dashboard
```

### 2. Filter Data
- Pilih unit layanan dari dropdown
- Pilih tahun
- Pilih periode (opsional)
- Klik "Filter"

### 3. Refresh Data Manual
- Klik tombol "Refresh Data" untuk memuat ulang via AJAX

### 4. Monitoring Alert
- Jika ada penurunan IKM > 2 poin, alert merah akan muncul otomatis

## Cache Implementation

```php
// Generate cache key unik
$cacheKey = "dashboard_data_" . md5($unitId . '_' . $tahun . '_' . $periodeId);

// Get from cache
$data = $this->cache->get($cacheKey);

// Save to cache (1 hour TTL)
$this->cache->save($cacheKey, $data, 3600);
```

## API Response Format

```json
{
    "status": "success",
    "data": {
        "tren": [...],
        "distribusi": [...],
        "rekap": [...],
        "alerts": [...]
    }
}
```

## Security

- Authentication required (filter: auth)
- Role-based access (Admin only)
- Session validation pada setiap request

## Cron Scheduler (Optional)

Untuk auto-refresh cache setiap jam:

```bash
# Crontab entry
0 * * * * php /path/to/project/spark cache:clear_dashboard
```

## Testing

### Unit Test Model
```bash
php spark test tests/models/DashboardModelTest.php
```

### Integration Test API
```bash
php spark test tests/api/DashboardApiTest.php
```

## Troubleshooting

### Cache tidak berfungsi
- Pastikan Redis running: `redis-cli ping`
- Check config Cache: `app/Config/Cache.php`

### Grafik tidak muncul
- Check Chart.js CDN loaded
- Verify data format JSON valid

### Alert tidak muncul
- Check threshold penurunan di query SQL
- Verify data periode sebelumnya tersedia

## Version History

- **v1.0.0**: Initial implementation
  - DashboardModel dengan 4 query methods
  - DashboardController dengan Redis cache
  - DashboardApiController untuk AJAX
  - View dengan Chart.js 4
  - Admin layout template

## Contact

Untuk pertanyaan atau issue, hubungi tim development.
