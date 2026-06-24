<?php

namespace App\Service\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Token\AccessToken;
use App\Entity\User;

class OAuth2Service
{
    private array $providers;
    private string $redirectUri;

    public function __construct(array $config, string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
        $this->providers = $this->initializeProviders($config);
    }

    /**
     * Initialize OAuth2 providers from config
     */
    private function initializeProviders(array $config): array
    {
        $providers = [];

        if (isset($config['google'])) {
            $providers['google'] = new Google([
                'clientId' => $config['google']['client_id'],
                'clientSecret' => $config['google']['client_secret'],
                'redirectUri' => $this->redirectUri . '?provider=google',
            ]);
        }

        if (isset($config['github'])) {
            $providers['github'] = new Github([
                'clientId' => $config['github']['client_id'],
                'clientSecret' => $config['github']['client_secret'],
                'redirectUri' => $this->redirectUri . '?provider=github',
            ]);
        }

        if (isset($config['facebook'])) {
            $providers['facebook'] = new Facebook([
                'clientId' => $config['facebook']['client_id'],
                'clientSecret' => $config['facebook']['client_secret'],
                'redirectUri' => $this->redirectUri . '?provider=facebook',
            ]);
        }

        // Add custom providers
        if (isset($config['custom'])) {
            foreach ($config['custom'] as $name => $providerConfig) {
                $providers[$name] = $this->createCustomProvider($providerConfig);
            }
        }

        return $providers;
    }

    /**
     * Create a custom OAuth2 provider
     */
    private function createCustomProvider(array $config): AbstractProvider
    {
        return new class($config) extends AbstractProvider {
            private array $config;

            public function __construct(array $config)
            {
                $this->config = $config;
                parent::__construct($config);
            }

            protected function getBaseAuthorizationUrl(): string
            {
                return $this->config['authorization_url'];
            }

            protected function getBaseAccessTokenUrl(array $params): string
            {
                return $this->config['token_url'];
            }

            protected function getResourceOwnerDetailsUrl(AccessToken $token): string
            {
                return $this->config['resource_owner_url'];
            }

            protected function getDefaultScopes(): array
            {
                return $this->config['scopes'] ?? [];
            }

            protected function checkResponse(array $response, $data): void
            {
                if (isset($response['error'])) {
                    throw new \Exception($response['error']);
                }
            }

            protected function createResourceOwner(array $response, AccessToken $token): \League\OAuth2\Client\Provider\ResourceOwnerInterface
            {
                return new class($response) implements \League\OAuth2\Client\Provider\ResourceOwnerInterface {
                    private array $response;

                    public function __construct(array $response)
                    {
                        $this->response = $response;
                    }

                    public function toArray(): array
                    {
                        return $this->response;
                    }

                    public function getId(): ?string
                    {
                        return $this->response['id'] ?? null;
                    }
                };
            }
        };
    }

    /**
     * Get authorization URL for a provider
     */
    public function getAuthorizationUrl(string $provider): string
    {
        if (!isset($this->providers[$provider])) {
            throw new \InvalidArgumentException("Provider '$provider' not configured");
        }

        return $this->providers[$provider]->getAuthorizationUrl();
    }

    /**
     * Get access token from authorization code
     */
    public function getAccessToken(string $provider, string $code): AccessToken
    {
        if (!isset($this->providers[$provider])) {
            throw new \InvalidArgumentException("Provider '$provider' not configured");
        }

        try {
            return $this->providers[$provider]->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get access token: ' . $e->getMessage());
        }
    }

    /**
     * Get resource owner details
     */
    public function getResourceOwner(string $provider, AccessToken $token): array
    {
        if (!isset($this->providers[$provider])) {
            throw new \InvalidArgumentException("Provider '$provider' not configured");
        }

        try {
            $resourceOwner = $this->providers[$provider]->getResourceOwner($token);
            return $resourceOwner->toArray();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get resource owner: ' . $e->getMessage());
        }
    }

    /**
     * Authenticate user with OAuth2 and return/create user
     */
    public function authenticate(string $provider, string $code, callable $userCallback): User
    {
        // Get access token
        $token = $this->getAccessToken($provider, $code);

        // Get user details from provider
        $details = $this->getResourceOwner($provider, $token);

        // Map provider-specific fields to common format
        $userData = $this->mapUserData($provider, $details);

        // Call callback to find or create user
        return $userCallback($provider, $userData, $token);
    }

    /**
     * Map provider-specific user data to common format
     */
    private function mapUserData(string $provider, array $details): array
    {
        switch ($provider) {
            case 'google':
                return [
                    'email' => $details['email'] ?? null,
                    'username' => $details['email'] ?? $details['id'],
                    'name' => $details['name'] ?? null,
                    'avatar' => $details['picture'] ?? null,
                    'provider_id' => $details['id'],
                ];

            case 'github':
                return [
                    'email' => $details['email'] ?? null,
                    'username' => $details['login'] ?? $details['id'],
                    'name' => $details['name'] ?? null,
                    'avatar' => $details['avatar_url'] ?? null,
                    'provider_id' => (string) $details['id'],
                ];

            case 'facebook':
                return [
                    'email' => $details['email'] ?? null,
                    'username' => $details['email'] ?? $details['id'],
                    'name' => $details['name'] ?? null,
                    'avatar' => isset($details['picture']['data']['url']) ? $details['picture']['data']['url'] : null,
                    'provider_id' => $details['id'],
                ];

            default:
                return [
                    'email' => $details['email'] ?? null,
                    'username' => $details['username'] ?? $details['id'],
                    'name' => $details['name'] ?? null,
                    'avatar' => $details['avatar'] ?? null,
                    'provider_id' => $details['id'],
                ];
        }
    }

    /**
     * Check if a provider is available
     */
    public function hasProvider(string $provider): bool
    {
        return isset($this->providers[$provider]);
    }

    /**
     * Get list of available providers
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
