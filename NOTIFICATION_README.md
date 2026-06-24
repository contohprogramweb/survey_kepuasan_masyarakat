# Sistem Notifikasi - Survey Management System

## Fitur yang Diimplementasikan

### 1. Notifikasi In-App
- ✅ Badge di header dashboard (menampilkan jumlah notifikasi belum dibaca)
- ✅ Dropdown daftar notifikasi dengan auto-refresh setiap 30 detik
- ✅ Tandai sebagai 'sudah dibaca' (per item atau semua)
- ✅ Indikator visual untuk notifikasi belum dibaca

### 2. Email Notification
- ✅ Menggunakan CodeIgniter Email Library
- ✅ Template email HTML + plain text
- ✅ Queue job: `EmailJob` untuk pemrosesan async
- ✅ Konfigurasi SMTP lengkap

### 3. WhatsApp Business API
- ✅ Integrasi dengan Meta Official Business Partner API
- ✅ Template messages pre-approved
- ✅ Queue job: `WhatsAppJob` untuk pemrosesan async
- ✅ Format nomor telepon otomatis

### 4. Scheduler Otomatis
- ✅ Command CLI: `php spark notification:scheduler`
- ✅ Cek kondisi setiap jam via cron
- ✅ 3 kondisi monitoring:
  - Periode survei akan berakhir (dalam 3 hari)
  - Target responden belum tercapai (<80% atau <3 hari)
  - IKM turun signifikan (>5%)

## Struktur File

```
app/
├── Commands/
│   └── NotificationScheduler.php    # Scheduler command
├── Config/
│   └── Email.php                    # Email configuration
├── Controllers/
│   └── NotificationController.php   # API & settings controller
├── Database/Migrations/
│   └── Mig01CreateNotifications.php # Database migration
├── Jobs/
│   ├── EmailJob.php                 # Email queue job
│   └── WhatsAppJob.php              # WhatsApp queue job
├── Models/
│   ├── NotificationModel.php        # Notification model
│   └── NotificationPreferenceModel.php # Preferences model
├── Services/
│   └── NotificationService.php      # Core notification service
└── Views/
    ├── email_templates/
    │   └── notification.php         # Email HTML template
    └── notifications/
        ├── component.php            # Frontend badge/dropdown
        └── settings.php             # Settings page
```

## Instalasi

### 1. Jalankan Migration
```bash
php spark migrate
```

### 2. Konfigurasi Environment (.env)

```env
# Email Configuration
email.fromEmail = noreply@example.com
email.fromName = "Survey Management System"
email.SMTPHost = smtp.gmail.com
email.SMTPPort = 587
email.SMTPUser = your-email@gmail.com
email.SMTPPass = your-app-password
email.SMTPCrypto = tls

# WhatsApp Business API
WHATSAPP_API_URL=https://graph.facebook.com/v17.0
WHATSAPP_ACCESS_TOKEN=your-access-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-number-id
WHATSAPP_TEMPLATE_NAME=survey_notification
WHATSAPP_LANGUAGE_CODE=id
```

### 3. Setup Cron Job (Scheduler)

Tambahkan ke crontab (`crontab -e`):

```bash
# Run notification scheduler every hour
0 * * * * cd /path/to/your/project && php spark notification:scheduler >> /var/log/notification_scheduler.log 2>&1
```

### 4. Routing (app/Config/Routes.php)

```php
// Notification Routes
$routes->group('notifications', ['filter' => 'auth'], function($routes) {
    $routes->get('settings', 'NotificationController::settings');
    $routes->post('update-settings', 'NotificationController::updateSettings');
    
    // AJAX Endpoints
    $routes->get('unread-count', 'NotificationController::unreadCount');
    $routes->get('get-list', 'NotificationController::getList');
    $routes->post('mark-as-read', 'NotificationController::markAsRead');
    $routes->post('mark-all-as-read', 'NotificationController::markAllAsRead');
});
```

### 5. Include Component di Layout

Di file layout utama (header/navbar), tambahkan:

```php
<!-- Di dalam <ul class="navbar-nav"> -->
<?= view('notifications/component') ?>
```

Pastikan Bootstrap Icons tersedia:
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
```

## Cara Penggunaan

### Mengirim Notifikasi Manual

```php
use App\Services\NotificationService;

$notificationService = new NotificationService();

// Kirim notifikasi ke user ID 1
$notificationService->send(
    userId: 1,
    title: 'Survei Baru Dibuat',
    message: 'Survei "Kepuasan Layanan Q1" telah dibuat dan siap untuk disebar.',
    type: 'success', // info, warning, danger, success
    data: [
        'survey_id' => 123,
        'url' => site_url('surveys/123')
    ]
);
```

### Mengatur Preferensi User

User dapat mengakses halaman pengaturan di: `/notifications/settings`

atau secara programatis:

```php
$notificationService->updatePreferences($userId, [
    'enable_inapp' => 1,
    'enable_email' => 1,
    'enable_whatsapp' => 0
]);
```

### Menjalankan Scheduler Manual (Testing)

```bash
php spark notification:scheduler
```

Output contoh:
```
Starting Notification Scheduler...
Checking conditions: 2024-01-15 14:00:00
Checking: Survey periods ending soon...
  → Sent 3 notifications for ending periods
Checking: Survey response targets not met...
  → Sent 5 notifications for unmet targets
Checking: IKM decrease detected...
  → No significant IKM drops

Scheduler completed. Conditions checked: 3, Notifications sent: 8
```

## WhatsApp Template Requirements

Untuk menggunakan WhatsApp Business API, Anda perlu:

1. **Meta Business Verification** - Verifikasi bisnis di Meta Business Manager
2. **Phone Number Registration** - Daftarkan nomor telepon bisnis
3. **Template Approval** - Buat dan ajukan template message untuk disetujui

Contoh template yang perlu diajukan:

```
Nama Template: survey_notification
Bahasa: Indonesia (id)
Kategori: UTILITY

Komponen BODY:
Halo {{1}},

{{2}}

Silakan kunjungi dashboard untuk informasi lebih lanjut.
```

## Troubleshooting

### Email tidak terkirim
- Periksa kredensial SMTP di `.env`
- Untuk Gmail, gunakan "App Password" bukan password biasa
- Pastikan port dan enkripsi sesuai (587+TLS atau 465+SSL)

### WhatsApp tidak terkirim
- Periksa access token masih valid
- Nomor telepon harus dalam format internasional (62xxx)
- Template harus sudah approved oleh Meta

### Scheduler tidak berjalan
- Pastikan cron job terkonfigurasi dengan benar
- Periksa permission file dan path
- Lihat log di `/var/log/notification_scheduler.log`

## Keamanan

- Semua endpoint API memerlukan autentikasi
- Data sensitif disimpan di environment variables
- Input validation pada semua form
- XSS protection pada output

## Dependencies

- CodeIgniter 4.x
- Bootstrap 5 (untuk UI component)
- Bootstrap Icons
- cURL (untuk WhatsApp API)
