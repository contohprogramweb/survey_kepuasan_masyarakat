# Multi-Provider Authentication System

Sistem autentikasi multi-provider dengan dukungan untuk:
- Login lokal (username + password bcrypt + MFA TOTP)
- OAuth 2.0 / OpenID Connect (Google, GitHub, Facebook, custom)
- SAML 2.0 (OneLogin/php-saml)
- LDAP (ext-ldap fallback)

## Fitur

1. **JWT dengan RS256** - Token asimetris untuk session
2. **Refresh Token Rotation** - Keamanan refresh token
3. **Session Timeout 30 menit** - Dapat dikonfigurasi
4. **MFA Wajib** - Untuk role Super Admin dan DPO
5. **RBAC** - 6 peran: Super Admin, Admin, Operator, Pimpinan, DPO, DevOps

## Struktur File

```
src/
├── Config/
│   └── AuthConfig.php          # Konfigurasi autentikasi
├── Controller/
│   └── AuthController.php      # Controller utama autentikasi
├── Entity/
│   └── User.php                # Entity user dengan RBAC
├── Middleware/
│   ├── Authenticate.php        # Middleware autentikasi JWT
│   ├── Authorize.php           # Middleware RBAC
│   └── MfaRequired.php         # Middleware MFA verification
├── Repository/
│   └── UserRepository.php      # Repository pattern untuk user
├── Service/
│   ├── JwtService.php          # JWT generate/verify RS256
│   ├── MfaService.php          # TOTP MFA service
│   ├── OAuth2/
│   │   └── OAuth2Service.php   # OAuth2 provider handler
│   ├── Saml/
│   │   └── SamlService.php     # SAML 2.0 handler
│   └── Ldap/
│       └── LdapService.php     # LDAP authentication
templates/
│   └── login.html.php          # Login page template
keys/
│   ├── jwt_private.pem         # JWT private key
│   └── jwt_public.pem          # JWT public key
```

## Instalasi Dependencies

```bash
composer require firebase/php-jwt
composer require league/oauth2-client
composer require onelogin/php-saml
composer require spomky-labs/otphp
```

## Konfigurasi

Edit `src/Config/AuthConfig.php` untuk mengatur:
- JWT keys path
- OAuth2 provider credentials
- SAML IdP settings
- LDAP connection details

## Generate JWT Keys

```bash
mkdir -p keys
openssl genrsa -out keys/jwt_private.pem 2048
openssl rsa -in keys/jwt_private.pem -pubout -out keys/jwt_public.pem
```

Atau gunakan method static:

```php
JwtService::generateKeyPair(
    'keys/jwt_private.pem',
    'keys/jwt_public.pem'
);
```

## Penggunaan

### Inisialisasi Services

```php
use App\Config\AuthConfig;
use App\Service\JwtService;
use App\Service\MfaService;
use App\Service\OAuth2\OAuth2Service;
use App\Service\Saml\SamlService;
use App\Service\Ldap\LdapService;
use App\Repository\UserRepository;
use App\Controller\AuthController;

// Load config
$config = AuthConfig::getAll();

// Initialize services
$jwtService = new JwtService(
    $config['jwt']['private_key_path'],
    $config['jwt']['public_key_path'],
    $config['jwt']['passphrase'],
    $config['jwt']['access_token_ttl'],
    $config['jwt']['refresh_token_ttl']
);

$mfaService = new MfaService(
    $config['mfa']['issuer'],
    $config['mfa']['digits'],
    $config['mfa']['period']
);

$oauth2Service = new OAuth2Service(
    $config['oauth2']['providers'],
    $config['oauth2']['redirect_uri']
);

$samlService = new SamlService($config['saml']);

$ldapService = new LdapService($config['ldap']);

$userRepository = new UserRepository();

// Initialize controller
$authController = new AuthController(
    $jwtService,
    $mfaService,
    $oauth2Service,
    $samlService,
    $ldapService,
    $userRepository,
    $config['session']['timeout']
);
```

### Middleware Usage

```php
use App\Middleware\Authenticate;
use App\Middleware\Authorize;
use App\Middleware\MfaRequired;

// Authentication middleware
$authenticate = new Authenticate($jwtService);

// Authorization middleware (RBAC)
$authorizeAdmin = Authorize::role('Admin');
$authorizeSuperAdmin = Authorize::superAdmin();
$authorizeAnyRole = Authorize::anyRole(['Admin', 'Super Admin']);

// MFA required middleware
$mfaRequired = MfaRequired::strict($mfaService);

// Chain middleware
$middlewareStack = [
    [$authenticate, 'handle'],
    [$authorizeAdmin, 'handle'],
    [$mfaRequired, 'handle'],
];
```

### Sample Users

| Username | Password | Role | MFA Required |
|----------|----------|------|--------------|
| superadmin | SuperAdmin123! | Super Admin | Yes |
| admin | Admin123! | Admin | No |
| operator | Operator123! | Operator | No |
| pimpinan | Pimpinan123! | Pimpinan | No |
| dpo | DPO123! | DPO | Yes |
| devops | DevOps123! | DevOps | No |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/auth/login` | Show login page |
| POST | `/auth/login` | Local login (username+password) |
| POST | `/auth/mfa/verify` | Verify MFA code |
| GET | `/auth/oauth2/{provider}` | OAuth2 login redirect |
| GET | `/auth/oauth2/callback` | OAuth2 callback handler |
| GET | `/auth/saml` | SAML login redirect |
| POST | `/auth/saml/callback` | SAML response handler |
| POST | `/auth/ldap` | LDAP login |
| POST | `/auth/refresh` | Refresh access token |
| POST | `/auth/logout` | Logout user |
| GET | `/auth/mfa/setup` | Setup MFA (QR code) |
| POST | `/auth/mfa/enable` | Enable MFA |
| GET | `/auth/session` | Get session info |

## Security Features

1. **Password Hashing**: bcrypt dengan cost factor default PHP
2. **JWT RS256**: Asymmetric signing dengan RSA 2048-bit
3. **Refresh Token Rotation**: Old token revoked when refreshed
4. **Timing Attack Prevention**: Constant-time comparison untuk password
5. **MFA Enforcement**: Wajib untuk role tertentu
6. **Session Timeout**: 30 menit idle timeout
7. **Secure Cookies**: HttpOnly, Secure, SameSite=Strict

## Role Permissions

| Permission | Super Admin | Admin | Operator | Pimpinan | DPO | DevOps |
|------------|-------------|-------|----------|---------|-----|--------|
| * (all) | ✓ | | | | | |
| user.read | ✓ | ✓ | ✓ | ✓ | ✓ | |
| user.write | ✓ | ✓ | ✓ | | | |
| user.delete | ✓ | ✓ | | | | |
| system.read | ✓ | ✓ | ✓ | | | ✓ |
| system.write | ✓ | ✓ | | | | ✓ |
| audit.read | ✓ | ✓ | | ✓ | ✓ | |
| report.read | ✓ | | | ✓ | | |
| report.write | ✓ | | | ✓ | | |
| data.read | ✓ | | | | ✓ | |
| data.write | ✓ | | | | ✓ | |
| data.delete | ✓ | | | | ✓ | |
| privacy.read | ✓ | | | | ✓ | |
| privacy.write | ✓ | | | | ✓ | |
| deploy.read | ✓ | | | | | ✓ |
| deploy.write | ✓ | | | | | ✓ |
| monitoring.read | ✓ | | | | | ✓ |

## Environment Variables

```env
# OAuth2 Providers
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret
FACEBOOK_CLIENT_ID=your-client-id
FACEBOOK_CLIENT_SECRET=your-client-secret

# SAML
SAML_IDP_ENTITY_ID=https://idp.example.com/saml
SAML_IDP_SSO_URL=https://idp.example.com/sso
SAML_IDP_SLS_URL=https://idp.example.com/slo
SAML_IDP_CERT=-----BEGIN CERTIFICATE-----...

# LDAP
LDAP_HOST=ldap.example.com
LDAP_PORT=389
LDAP_USE_TLS=false
LDAP_BASE_DN=dc=example,dc=com
LDAP_BIND_DN=cn=admin,dc=example,dc=com
LDAP_BIND_PASSWORD=secret
```

## License

MIT License
