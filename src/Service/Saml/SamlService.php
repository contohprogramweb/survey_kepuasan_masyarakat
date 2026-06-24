<?php

namespace App\Service\Saml;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use App\Entity\User;

class SamlService
{
    private Auth $auth;
    private array $settings;

    public function __construct(array $config)
    {
        $this->settings = $this->buildSettings($config);
        $this->auth = new Auth($this->settings);
    }

    /**
     * Build SAML settings from config
     */
    private function buildSettings(array $config): array
    {
        return [
            'strict' => $config['strict'] ?? true,
            'debug' => $config['debug'] ?? false,
            'baseurl' => $config['base_url'],
            'sp' => [
                'entityId' => $config['sp']['entity_id'],
                'assertionConsumerService' => [
                    'url' => $config['sp']['acs_url'],
                ],
                'singleLogoutService' => [
                    'url' => $config['sp']['sls_url'] ?? '',
                ],
                'NameIDFormat' => $config['sp']['name_id_format'] ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'x509cert' => $config['sp']['x509cert'] ?? '',
                'privateKey' => $config['sp']['private_key'] ?? '',
            ],
            'idp' => [
                'entityId' => $config['idp']['entity_id'],
                'singleSignOnService' => [
                    'url' => $config['idp']['sso_url'],
                ],
                'singleLogoutService' => [
                    'url' => $config['idp']['sls_url'] ?? '',
                ],
                'x509cert' => $config['idp']['x509cert'],
            ],
            'security' => $config['security'] ?? [
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
     * Generate SAML login request and redirect URL
     */
    public function login(string $returnTo = null): string
    {
        return $this->auth->login($returnTo);
    }

    /**
     * Process SAML response after authentication
     */
    public function processResponse(): void
    {
        $this->auth->processResponse();

        $errors = $this->auth->getErrors();
        if (!empty($errors)) {
            throw new Error('SAML Response error: ' . implode(', ', $errors));
        }
    }

    /**
     * Check if user is authenticated via SAML
     */
    public function isAuthenticated(): bool
    {
        return $this->auth->isAuthenticated();
    }

    /**
     * Get SAML attributes from authenticated user
     */
    public function getAttributes(): array
    {
        return $this->auth->getAttributes();
    }

    /**
     * Get NameID from SAML response
     */
    public function getNameId(): ?string
    {
        return $this->auth->getNameId();
    }

    /**
     * Get NameID format from SAML response
     */
    public function getNameIdFormat(): ?string
    {
        return $this->auth->getNameIdFormat();
    }

    /**
     * Get NameID NQ from SAML response
     */
    public function getNameIdNq(): ?string
    {
        return $this->auth->getNameIdNq();
    }

    /**
     * Authenticate user with SAML and return/create user
     */
    public function authenticate(callable $userCallback): User
    {
        // Process the SAML response
        $this->processResponse();

        // Get user attributes from SAML
        $attributes = $this->getAttributes();
        $nameId = $this->getNameId();

        // Map SAML attributes to user data
        $userData = $this->mapUserData($attributes, $nameId);

        // Call callback to find or create user
        return $userCallback('saml', $userData);
    }

    /**
     * Map SAML attributes to common user data format
     */
    private function mapUserData(array $attributes, ?string $nameId): array
    {
        // Common attribute mappings (adjust based on your IdP)
        $email = $this->getAttributeValue($attributes, ['email', 'mail', 'EmailAddress', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress']);
        $username = $this->getAttributeValue($attributes, ['username', 'uid', 'UserName', 'name']) ?? $nameId;
        $firstName = $this->getAttributeValue($attributes, ['firstName', 'givenName', 'first_name', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname']);
        $lastName = $this->getAttributeValue($attributes, ['lastName', 'sn', 'familyName', 'last_name', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname']);
        $roles = $this->getAttributeValue($attributes, ['roles', 'role', 'groups', 'group', 'memberOf']) ?? [];

        // Ensure roles is an array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return [
            'email' => $email,
            'username' => $username,
            'name' => trim(($firstName ?? '') . ' ' . ($lastName ?? '')),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'provider_id' => $nameId,
            'roles' => $roles,
            'attributes' => $attributes,
        ];
    }

    /**
     * Get first matching attribute value
     */
    private function getAttributeValue(array $attributes, array $possibleNames): ?string
    {
        foreach ($possibleNames as $name) {
            if (isset($attributes[$name])) {
                $value = $attributes[$name];
                
                // Handle array values
                if (is_array($value)) {
                    return $value[0] ?? null;
                }
                
                return $value;
            }
        }
        
        return null;
    }

    /**
     * Generate SAML logout request
     */
    public function logout(?string $nameId = null, ?string $nameIdFormat = null, ?string $nameIdNq = null): string
    {
        return $this->auth->logout('', [], $nameId, $nameIdFormat, $nameIdNq);
    }

    /**
     * Process SAML logout response
     */
    public function processLogout(): void
    {
        $this->auth->processSlo();
        
        $errors = $this->auth->getErrors();
        if (!empty($errors)) {
            throw new Error('SAML Logout error: ' . implode(', ', $errors));
        }
    }

    /**
     * Get SAML metadata for this SP
     */
    public function getMetadata(): string
    {
        $metadata = $this->auth->getSettings()->getSPMetadata();
        
        $errors = $this->auth->getSettings()->validateMetadata($metadata);
        if (!empty($errors)) {
            throw new Error('Invalid SAML Metadata: ' . implode(', ', $errors));
        }
        
        return $metadata;
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        $errors = $this->auth->getErrors();
        if (!empty($errors)) {
            return implode(', ', $errors);
        }
        return null;
    }

    /**
     * Get auth object for advanced usage
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }
}
