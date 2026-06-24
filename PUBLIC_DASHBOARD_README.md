# Dashboard Publik IKM - Implementasi SRS F-20 & UC-20

## Overview
Dashboard publik yang menampilkan data Indeks Kepuasan Masyarakat (IKM) secara transparan untuk masyarakat umum. Tidak memerlukan login dan hanya menampilkan periode yang telah dipublikasikan (`is_published = 1`).

## Fitur Utama

### 1. Read-Only Access
- ✅ Tidak memerlukan autentikasi/login
- ✅ Hanya menampilkan data dari periode dengan `is_published = 1`
- ✅ Server-side rendering dengan cache Redis (1 jam TTL)

### 2. SEO Optimization
- ✅ Meta tags lengkap (title, description, keywords)
- ✅ Open Graph tags untuk social media sharing
- ✅ Twitter Card support
- ✅ Canonical URL
- ✅ JSON-LD structured data (Schema.org Dataset)

### 3. Visualisasi Data
- ✅ **Grafik Tren IKM** (Chart.js 4 line chart)
- ✅ **Distribusi Jawaban per Unsur** (doughnut chart)
- ✅ **Summary Cards**: Total Responden, Nilai IKM, Mutu Pelayanan, Unit Layanan
- ✅ **Tabel Rekapitulasi**: Periode, Tahun, Total Responden, Nilai IKM, Mutu, Delta

### 4. Filter Data
- ✅ Filter berdasarkan Unit Layanan
- ✅ Filter berdasarkan Tahun
- ✅ Filter berdasarkan Periode
- ✅ AJAX refresh button

### 5. Responsive Design
- ✅ Bootstrap 5.3.2
- ✅ Mobile-friendly layout
- ✅ Accessibility features (skip links, focus states)
- ✅ Reduced motion support

## Struktur File

```
app/
├── Controllers/
│   └── PublicController/
│       ├── PublicDashboardController.php    # Controller utama dashboard publik
│       └── LandingController.php            # Updated (dashboard method deprecated)
├── Models/
│   ├── DashboardModel.php                   # Updated: methods public_* added
│   └── PeriodeModel.php                     # New model for periode queries
├── Views/
│   └── public/
│       └── dashboard_ikm.php                # View dashboard publik lengkap
└── Config/
    └── Routes.php                           # Updated routes
```

## Routes

```php
// Dashboard Publik (tidak perlu login)
GET /dashboard                    -> PublicDashboardController::index
GET /public/dashboard/data        -> PublicDashboardController::getData (API endpoint)
```

## Database Requirements

Tabel `tb_periode` harus memiliki kolom:
- `is_published` (TINYINT): Flag untuk publikasi periode (0 = draft, 1 = published)

## API Endpoint

### GET /public/dashboard/data

**Parameters:**
- `unit_id` (optional): Filter by unit ID
- `tahun` (optional): Filter by year (default: current year)
- `periode_id` (optional): Filter by period ID

**Response:**
```json
{
  "status": "success",
  "data": {
    "tren": [...],
    "distribusi": [...],
    "rekap": [...],
    "summary": {
      "total_responden": 150,
      "nilai_ikm": 82.45,
      "mutu_pelayanan": "Sangat Baik",
      "total_unit": 5
    }
  }
}
```

## Caching Strategy

- **Cache Driver**: Redis (configured in `app/Config/Cache.php`)
- **TTL**: 3600 seconds (1 hour)
- **Cache Keys**: 
  - `public_dashboard_{md5_hash}` untuk HTML rendering
  - `public_dashboard_api_{md5_hash}` untuk API responses

## JSON-LD Structured Data

Dashboard menggunakan Schema.org `Dataset` type untuk SEO:

```json
{
  "@context": "https://schema.org",
  "@type": "Dataset",
  "name": "Dashboard Transparansi IKM",
  "description": "Data transparansi Indeks Kepuasan Masyarakat",
  "publisher": {
    "@type": "GovernmentOrganization",
    "name": "Pemerintah Daerah"
  },
  "variableMeasured": [
    {"@type": "PropertyValue", "name": "Nilai IKM"},
    {"@type": "PropertyValue", "name": "Mutu Pelayanan"}
  ]
}
```

## Mutu Pelayanan Classification

| Nilai IKM | Kategori | Badge Color |
|-----------|----------|-------------|
| ≥ 85      | Sangat Baik | Green (success) |
| 70 - 84   | Baik | Blue (primary) |
| 55 - 69   | Kurang Baik | Yellow (warning) |
| < 55      | Tidak Baik | Red (danger) |

## Testing

### Manual Testing
1. Akses `/dashboard` tanpa login
2. Verifikasi hanya periode dengan `is_published = 1` yang muncul
3. Test filter combinations
4. Check JSON-LD di Google Rich Results Test
5. Verify responsive design di berbagai device

### Automated Testing (Optional)
```bash
# Test API endpoint
curl "http://localhost:8080/public/dashboard/data?tahun=2024"

# Test with filters
curl "http://localhost:8080/public/dashboard/data?unit_id=1&tahun=2024&periode_id=2"
```

## Cron Job untuk Sitemap (Optional)

Untuk auto-generate sitemap.xml yang include dashboard URL:

```bash
# Tambahkan ke crontab
0 * * * * cd /path/to/app && php spark seo:sitemap
```

## Security Considerations

- ✅ No authentication required (public access)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (esc() function di views)
- ✅ Rate limiting recommended untuk API endpoint
- ✅ Cache prevents database overload

## Performance Tips

1. Enable Redis caching (already implemented)
2. Use CDN for Chart.js dan Bootstrap
3. Minify CSS/JS in production
4. Consider lazy loading untuk charts
5. Set appropriate cache headers

## Troubleshooting

### Issue: Dashboard tidak menampilkan data
- Check `is_published` flag di tabel `tb_periode`
- Verify Redis connection
- Check database query di `DashboardModel::getPublic*()` methods

### Issue: Charts tidak muncul
- Verify Chart.js CDN is accessible
- Check browser console for JavaScript errors
- Ensure data arrays are not empty

### Issue: SEO not working
- Validate JSON-LD di https://validator.schema.org/
- Check meta tags di page source
- Verify canonical URL is correct

## Future Enhancements

- [ ] Export to PDF/Excel functionality
- [ ] Real-time updates via WebSocket
- [ ] Multi-language support (i18n)
- [ ] Advanced filtering (date range)
- [ ] Comparison mode (side-by-side units)
- [ ] Downloadable reports

## References

- SRS F-20: Dashboard Publik Requirements
- UC-20: Use Case Dashboard Publik
- Schema.org Dataset: https://schema.org/Dataset
- Chart.js Documentation: https://www.chartjs.org/
