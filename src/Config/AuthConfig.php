<?php

namespace App\Config;

/**
 * Authentication Configuration
 * 
 * Configure all authentication providers and settings here
 */
class AuthConfig
{
    /**
     * JWT Configuration
     */
    public static function getJwtConfig(): array
    {
        return [
            'private_key_path' => __DIR__ . '/../../keys/jwt_private.pem',
            'public_key_path' => __DIR__ . '/../../keys/jwt_public.pem',
            'passphrase' => '', // Optional passphrase for private key
            'access_token_ttl' => 1800, // 30 minutes
            'refresh_token_ttl' => 604800, // 7 days
        ];
    }

    /**
     * MFA Configuration
     */
    public static function getMfaConfig(): array
    {
        return [
            'issuer' => 'MyApp',
            'digits' => 6,
            'period' => 30,
            'required_roles' => ['Super Admin', 'DPO'],
        ];
    }

    /**
     * OAuth2 Configuration
     */
    public static function getOAuth2Config(): array
    {
        return [
            'redirect_uri' => 'https://yourapp.com/auth/oauth2/callback',
            'providers' => [
                'google' => [
                    'client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'your-google-client-id',
                    'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'your-google-client-secret',
                ],
                'github' => [
                    'client_id' => getenv('GITHUB_CLIENT_ID') ?: 'your-github-client-id',
                    'client_secret' => getenv('GITHUB_CLIENT_SECRET') ?: 'your-github-client-secret',
                ],
                'facebook' => [
                    'client_id' => getenv('FACEBOOK_CLIENT_ID') ?: 'your-facebook-client-id',
                    'client_secret' => getenv('FACEBOOK_CLIENT_SECRET') ?: 'your-facebook-client-secret',
                ],
                // Custom OIDC provider example
                'custom_oidc' => [
                    'authorization_url' => 'https://idp.example.com/authorize',
                    'token_url' => 'https://idp.example.com/token',
                    'resource_owner_url' => 'https://idp.example.com/userinfo',
                    'scopes' => ['openid', 'profile', 'email'],
                ],
            ],
        ];
    }

    /**
     * SAML Configuration
     */
    public static function getSamlConfig(): array
    {
        return [
            'strict' => true,
            'debug' => false,
            'base_url' => 'https://yourapp.com',
            'sp' => [
                'entity_id' => 'https://yourapp.com/saml/metadata',
                'acs_url' => 'https://yourapp.com/auth/saml/callback',
                'sls_url' => 'https://yourapp.com/auth/saml/logout',
                'name_id_format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'x509cert' => '', // Optional SP certificate
                'private_key' => '', // Optional SP private key
            ],
            'idp' => [
                'entity_id' => getenv('SAML_IDP_ENTITY_ID') ?: 'https://idp.example.com/saml',
                'sso_url' => getenv('SAML_IDP_SSO_URL') ?: 'https://idp.example.com/sso',
                'sls_url' => getenv('SAML_IDP_SLS_URL') ?: 'https://idp.example.com/slo',
                'x509cert' => getenv('SAML_IDP_CERT') ?: '-----BEGIN CERTIFICATE-----...',
            ],
            'security' => [
                'authnRequestsSigned' => false,
                'logoutRequestSigned' => false,
                'logoutResponseSigned' => false,
                'signMetadata' => false,
                'wantMessagesSigned' => false,
                'wantAssertionsSigned' => false,
                'wantAssertionsEncrypted' => false,
                'wantNameId' => true,
                'wantNameIdEncrypted' => false,
                'wantXMLValidation' => true,
                'requestedAuthnContext' => false,
                'lowercaseUrlencoding' => false,
            ],
        ];
    }

    /**
     * LDAP Configuration
     */
    public static function getLdapConfig(): array
    {
        return [
            'enabled' => extension_loaded('ldap'),
            'host' => getenv('LDAP_HOST') ?: 'ldap.example.com',
            'port' => (int) (getenv('LDAP_PORT') ?: 389),
            'use_tls' => (bool) (getenv('LDAP_USE_TLS') ?: false),
            'base_dn' => getenv('LDAP_BASE_DN') ?: 'dc=example,dc=com',
            'bind_dn' => getenv('LDAP_BIND_DN') ?: 'cn=admin,dc=example,dc=com',
            'bind_password' => getenv('LDAP_BIND_PASSWORD') ?: 'secret',
            'user_ou' => 'Users',
            'object_class' => 'inetOrgPerson',
            'uid_attribute' => 'uid',
            'email_attribute' => 'mail',
            'name_attribute' => 'cn',
            'groups_attribute' => 'memberOf',
        ];
    }

    /**
     * Session Configuration
     */
    public static function getSessionConfig(): array
    {
        return [
            'timeout' => 1800, // 30 minutes
            'cookie_name' => 'auth_session',
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
        ];
    }

    /**
     * Role Configuration
     */
    public static function getRoles(): array
    {
        return [
            'Super Admin' => [
                'description' => 'Full system access',
                'mfa_required' => true,
                'permissions' => ['*'],
            ],
            'Admin' => [
                'description' => 'Administrative access',
                'mfa_required' => false,
                'permissions' => ['user.read', 'user.write', 'user.delete', 'system.read', 'system.write', 'audit.read'],
            ],
            'Operator' => [
                'description' => 'Standard operator access',
                'mfa_required' => false,
                'permissions' => ['user.read', 'user.write', 'system.read'],
            ],
            'Pimpinan' => [
                'description' => 'Executive/Management access',
                'mfa_required' => false,
                'permissions' => ['user.read', 'report.read', 'report.write', 'audit.read'],
            ],
            'DPO' => [
                'description' => 'Data Protection Officer',
                'mfa_required' => true,
                'permissions' => ['user.read', 'data.read', 'data.write', 'data.delete', 'audit.read', 'privacy.read', 'privacy.write'],
            ],
            'DevOps' => [
                'description' => 'Development Operations',
                'mfa_required' => false,
                'permissions' => ['system.read', 'system.write', 'deploy.read', 'deploy.write', 'monitoring.read'],
            ],
        ];
    }

    /**
     * Get all configuration as a single array
     */
    public static function getAll(): array
    {
        return [
            'jwt' => self::getJwtConfig(),
            'mfa' => self::getMfaConfig(),
            'oauth2' => self::getOAuth2Config(),
            'saml' => self::getSamlConfig(),
            'ldap' => self::getLdapConfig(),
            'session' => self::getSessionConfig(),
            'roles' => self::getRoles(),
        ];
    }
}
