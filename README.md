# Multi-Provider Authentication System (CodeIgniter 4)

Sistem autentikasi multi-provider dengan dukungan untuk:
- Login lokal (username + password bcrypt + MFA TOTP)
- OAuth 2.0 / OpenID Connect (Google, GitHub, Facebook)
- SAML 2.0 dan LDAP

## Fitur Utama

1. **JWT RS256** - Token asimetris untuk session
2. **Refresh Token Rotation** - Keamanan refresh token
3. **MFA Wajib** - Untuk role Super Admin dan DPO
4. **RBAC** - 6 peran: Super Admin, Admin, Operator, Pimpinan, DPO, DevOps

## Instalasi & Konfigurasi

```bash
# Install dependencies
composer install

# Generate JWT keys
mkdir -p keys
openssl genrsa -out keys/jwt_private.pem 2048
openssl rsa -in keys/jwt_private.pem -pubout -out keys/jwt_public.pem

# Setup environment
cp .env.example .env
# Edit .env sesuai konfigurasi database dan provider OAuth/SAML/LDAP
```

## Menjalankan Aplikasi

```bash
php spark serve
```

Akses: `http://localhost:8080`

## User Default

| Username | Password | Role | MFA |
|----------|----------|------|-----|
| superadmin | SuperAdmin123! | Super Admin | Ya |
| admin | Admin123! | Admin | Tidak |
| operator | Operator123! | Operator | Tidak |
| pimpinan | Pimpinan123! | Pimpinan | Tidak |
| dpo | DPO123! | DPO | Ya |
| devops | DevOps123! | DevOps | Tidak |

## API Endpoints

- `GET/POST /auth/login` - Login page & local login
- `POST /auth/mfa/verify` - Verifikasi kode MFA
- `GET /auth/oauth2/{provider}` - OAuth2 login
- `POST /auth/refresh` - Refresh access token
- `POST /auth/logout` - Logout
- `GET /auth/mfa/setup` - Setup MFA (QR code)

## Dokumentasi Lengkap

Lihat file asli untuk detail konfigurasi, middleware, dan permission matrix lengkap.

## License

MIT License
