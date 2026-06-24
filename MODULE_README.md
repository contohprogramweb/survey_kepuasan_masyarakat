# Modul Manajemen Pengguna

Berdasarkan **SRS F-01** dan **UC-02**, modul ini mengimplementasikan sistem manajemen pengguna lengkap dengan fitur CRUD, role assignment, password reset, aktivasi/deaktivasi akun, dan audit logging.

## 📁 Struktur File

```
/workspace
├── src/
│   ├── Controller/
│   │   └── UsersController.php       # Controller untuk user management
│   ├── Model/
│   │   └── UserModel.php             # Model user dengan soft delete
│   └── Service/
│       └── AuditLogService.php       # Service untuk audit logging
├── templates/users/
│   ├── index.php                     # View: Daftar pengguna (DataTables)
│   ├── create.php                    # View: Form tambah user
│   ├── edit.php                      # View: Form edit user
│   └── show.php                      # View: Detail user + audit log
├── public/assets/                    # Asset CSS/JS (CDN)
└── database_schema.sql               # Database schema
```

## 🔐 Fitur Utama

### 1. CRUD Pengguna (Super Admin Only)
- ✅ Create: Tambah user baru dengan validasi lengkap
- ✅ Read: Lihat daftar dan detail user
- ✅ Update: Edit informasi user
- ✅ Delete: Soft delete (tidak bisa hapus Super Admin)

### 2. Role Assignment (Super Admin Only)
- 6 Role tersedia:
  - **Super Admin** - Akses penuh, tidak bisa dihapus/dinonaktifkan
  - **Admin** - Reset password, aktivasi/deaktivasi user
  - **Operator** - User operasional
  - **Pimpinan** - User level pimpinan
  - **DPO** - Data Protection Officer
  - **DevOps** - Infrastructure & deployment

### 3. Reset Password (Admin+)
- Admin dan Super Admin dapat mereset password user lain
- Validasi password minimal 8 karakter
- Tidak bisa reset password Super Admin kecuali oleh Super Admin lain
- Semua aksi tercatat di audit log

### 4. Aktivasi/Deaktivasi Akun
- Admin+ dapat mengaktifkan/nonaktifkan user
- Super Admin tidak dapat dinonaktifkan
- Status: `active`, `inactive`, `suspended`

### 5. DataTables Server-Side Processing
- Pagination, sorting, searching server-side
- Filter berdasarkan role dan status
- Responsive design
- Loading indicator

### 6. Audit Logging
- Semua aksi CRUD tercatat di tabel `audit_log`
- Menyimpan: old values, new values, IP address, user agent
- Dapat dilihat di halaman detail user
- Action types: CREATE, UPDATE, DELETE, SOFT_DELETE, ACTIVATE, DEACTIVATE, PASSWORD_RESET, ROLE_CHANGE

## 🛡️ Keamanan

### Unique Validation
- Username harus unik
- Email harus unik
- Validasi exclude current user saat update

### Proteksi Super Admin
- ❌ Tidak bisa dihapus (hanya soft delete yang diblokir)
- ❌ Tidak bisa dinonaktifkan
- ❌ Role tidak bisa diubah
- ✅ Hanya Super Admin yang bisa reset password Super Admin lain

### Soft Delete
- Field `deleted_at` menandai user dihapus
- Query otomatis filter `deleted_at IS NULL`
- Audit trail tetap terjaga

## 📊 Database Schema

### Tabel `users`
```sql
- id, username, email, password_hash
- full_name, role, status
- mfa_enabled, mfa_secret
- created_at, updated_at, deleted_at
- created_by, updated_by, deleted_by (FK ke users)
- activated_at, deactivated_at, suspended_at
- suspension_reason, password_reset_at
- last_login_at, last_login_ip
```

### Tabel `audit_log`
```sql
- id, user_id, action, entity_type, entity_id
- old_values (JSON), new_values (JSON)
- ip_address, user_agent, created_at
```

## 🚀 Cara Penggunaan

### 1. Setup Database
```bash
mysql -u root -p < database_schema.sql
```

### 2. Inisialisasi Controller
```php
<?php
require_once 'src/Controller/UsersController.php';

$db = new PDO('mysql:host=localhost;dbname=yourdb', 'user', 'pass');
$controller = new UsersController($db);

// Routing example
switch ($_GET['action'] ?? 'index') {
    case 'index':
        $controller->index();
        break;
    case 'data':
        $controller->getData();
        break;
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    // ... dst
}
```

### 3. API Endpoints

| Method | Endpoint | Permission | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/users` | Admin+ | Halaman daftar user |
| GET | `/api/users/data` | Admin+ | DataTables server-side |
| GET | `/users/create` | Super Admin | Form tambah user |
| POST | `/api/users` | Super Admin | Simpan user baru |
| GET | `/users/{id}/edit` | Super Admin | Form edit user |
| POST | `/api/users/{id}` | Super Admin | Update user |
| GET | `/users/{id}` | Admin+ | Detail user |
| DELETE | `/api/users/{id}` | Super Admin | Hapus user (soft delete) |
| POST | `/api/users/{id}/reset-password` | Admin+ | Reset password |
| POST | `/api/users/{id}/activate` | Admin+ | Aktifkan user |
| POST | `/api/users/{id}/deactivate` | Admin+ | Nonaktifkan user |

## 🎨 UI Features

### DataTables Configuration
- **Server-side processing** untuk performa optimal
- **Responsive** design untuk mobile
- **Custom filters**: Role dan Status dropdown
- **SweetAlert2** untuk konfirmasi aksi destruktif
- **Loading spinner** saat proses AJAX

### Action Buttons
| Button | Permission | Deskripsi |
|--------|------------|-----------|
| 👁️ View | Admin+ | Lihat detail user |
| ✏️ Edit | Super Admin | Edit user |
| 🔑 Reset Password | Admin+ | Reset password user |
| ✅ Activate | Admin+ | Aktifkan user (jika inactive) |
| 🚫 Deactivate | Admin+ | Nonaktifkan user (jika active, bukan Super Admin) |
| 🗑️ Delete | Super Admin | Hapus user (bukan Super Admin) |

## 📝 Validasi

### Create User
- Username: required, min 3 karakter, unique
- Email: required, valid format, unique
- Full Name: required
- Password: required, min 8 karakter, match confirmation
- Role: required, valid enum value
- Status: default 'active'

### Update User
- Email: required, valid format, unique (exclude self)
- Full Name: required
- Role: required, valid enum (tidak bisa ubah Super Admin)
- Status: valid enum value

## 🔍 Audit Log Example

```json
{
  "action": "CREATE",
  "entity_type": "user",
  "old_values": null,
  "new_values": {
    "username": "newuser",
    "email": "new@example.com",
    "full_name": "New User",
    "role": "operator"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2024-01-15 10:30:00"
}
```

## 🧪 Sample Users

| Username | Password | Role | Status |
|----------|----------|------|--------|
| superadmin | admin123!@# | super_admin | active |
| admin | admin123!@# | admin | active |
| operator | operator123!@# | operator | active |

## ⚠️ Catatan Penting

1. **Super Admin Protection**: Selalu ada minimal 1 Super Admin aktif
2. **Audit Trail**: Semua aksi penting wajib dicatat
3. **Soft Delete**: Data tidak benar-benar hilang, bisa di-recover jika perlu
4. **Session Management**: Pastikan session sudah dimulai sebelum akses controller
5. **HTTPS**: Gunakan HTTPS untuk production environment

## 📦 Dependencies

- **PHP 7.4+** dengan PDO
- **MySQL 5.7+** atau MariaDB
- **jQuery 3.6+** (CDN)
- **Bootstrap 4.6+** (CDN)
- **DataTables 1.13+** (CDN)
- **SweetAlert2 11+** (CDN)
- **Font Awesome 5+** (CDN)

## 📄 License

Internal Use Only - Proprietary
