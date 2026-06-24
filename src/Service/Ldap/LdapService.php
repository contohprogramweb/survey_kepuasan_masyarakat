<?php

namespace App\Service\Ldap;

use App\Entity\User;

class LdapService
{
    private ?resource $connection;
    private string $host;
    private int $port;
    private bool $useTls;
    private string $baseDn;
    private string $bindDn;
    private string $bindPassword;
    private array $userConfig;

    public function __construct(array $config)
    {
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? 389;
        $this->useTls = $config['use_tls'] ?? false;
        $this->baseDn = $config['base_dn'] ?? 'dc=example,dc=com';
        $this->bindDn = $config['bind_dn'] ?? '';
        $this->bindPassword = $config['bind_password'] ?? '';
        
        $this->userConfig = [
            'ou' => $config['user_ou'] ?? 'Users',
            'object_class' => $config['object_class'] ?? 'inetOrgPerson',
            'uid_attribute' => $config['uid_attribute'] ?? 'uid',
            'email_attribute' => $config['email_attribute'] ?? 'mail',
            'name_attribute' => $config['name_attribute'] ?? 'cn',
            'groups_attribute' => $config['groups_attribute'] ?? 'memberOf',
        ];

        $this->connection = null;
    }

    /**
     * Connect to LDAP server
     */
    public function connect(): bool
    {
        // Check if LDAP extension is available
        if (!extension_loaded('ldap')) {
            throw new \RuntimeException('LDAP extension is not loaded');
        }

        // Connect to LDAP server
        $this->connection = ldap_connect($this->host, $this->port);
        
        if ($this->connection === false) {
            throw new \RuntimeException('Failed to connect to LDAP server');
        }

        // Set LDAP options
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

        // Start TLS if configured
        if ($this->useTls) {
            if (!ldap_start_tls($this->connection)) {
                throw new \RuntimeException('Failed to start TLS connection');
            }
        }

        return true;
    }

    /**
     * Bind to LDAP server
     */
    public function bind(?string $dn = null, ?string $password = null): bool
    {
        if ($this->connection === null) {
            $this->connect();
        }

        $bindDn = $dn ?? $this->bindDn;
        $bindPassword = $password ?? $this->bindPassword;

        $result = ldap_bind($this->connection, $bindDn, $bindPassword);
        
        if (!$result) {
            throw new \RuntimeException('LDAP bind failed: ' . ldap_error($this->connection));
        }

        return true;
    }

    /**
     * Authenticate user with LDAP
     */
    public function authenticate(string $username, string $password): ?User
    {
        try {
            // Connect and bind with service account
            $this->connect();
            $this->bind();

            // Find user
            $userEntry = $this->findUser($username);
            
            if ($userEntry === null) {
                return null;
            }

            // Try to bind with user credentials
            $userDn = $userEntry['dn'];
            $testBind = @ldap_bind($this->connection, $userDn, $password);
            
            if (!$testBind) {
                return null;
            }

            // Re-bind with service account to fetch attributes
            $this->bind();

            // Create user entity
            return $this->createUserFromEntry($userEntry);
            
        } catch (\Exception $e) {
            // Log error but don't expose details
            error_log('LDAP authentication error: ' . $e->getMessage());
            return null;
        } finally {
            $this->disconnect();
        }
    }

    /**
     * Find user by username
     */
    private function findUser(string $username): ?array
    {
        $ou = $this->userConfig['ou'];
        $uidAttribute = $this->userConfig['uid_attribute'];
        $objectClass = $this->userConfig['object_class'];

        $searchBase = "ou=$ou," . $this->baseDn;
        $filter = "(&($uidAttribute=" . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . ")(objectClass=$objectClass))";
        
        $attributes = [
            $this->userConfig['uid_attribute'],
            $this->userConfig['email_attribute'],
            $this->userConfig['name_attribute'],
            $this->userConfig['groups_attribute'],
            'dn',
        ];

        $searchResult = ldap_search($this->connection, $searchBase, $filter, $attributes);
        
        if ($searchResult === false) {
            return null;
        }

        $entries = ldap_get_entries($this->connection, $searchResult);
        
        if ($entries === false || $entries['count'] === 0) {
            return null;
        }

        return $entries[0];
    }

    /**
     * Create User entity from LDAP entry
     */
    private function createUserFromEntry(array $entry): User
    {
        $uidAttribute = $this->userConfig['uid_attribute'];
        $emailAttribute = $this->userConfig['email_attribute'];
        $nameAttribute = $this->userConfig['name_attribute'];
        $groupsAttribute = $this->userConfig['groups_attribute'];

        $username = $this->getAttributeValue($entry, $uidAttribute);
        $email = $this->getAttributeValue($entry, $emailAttribute);
        $name = $this->getAttributeValue($entry, $nameAttribute);
        $groups = $this->getAttributeValues($entry, $groupsAttribute);

        // Map LDAP groups to application roles
        $roles = $this->mapGroupsToRoles($groups);

        // Generate a unique ID based on DN
        $id = crc32($entry['dn']);

        $user = new User(
            $id,
            $username,
            $email ?? "$username@ldap.local",
            '', // No password hash for LDAP users
            $roles,
            null, // TOTP secret
            false, // MFA enabled (can be configured)
            'ldap',
            [
                'dn' => $entry['dn'],
                'name' => $name,
                'ldap_groups' => $groups,
            ]
        );

        return $user;
    }

    /**
     * Map LDAP groups to application roles
     */
    private function mapGroupsToRoles(array $groups): array
    {
        $roleMapping = [
            'CN=Super Admin' => 'Super Admin',
            'CN=Admin' => 'Admin',
            'CN=Operator' => 'Operator',
            'CN=Pimpinan' => 'Pimpinan',
            'CN=DPO' => 'DPO',
            'CN=DevOps' => 'DevOps',
        ];

        $roles = [];
        foreach ($groups as $group) {
            foreach ($roleMapping as $ldapGroup => $appRole) {
                if (stripos($group, $ldapGroup) !== false) {
                    $roles[] = $appRole;
                }
            }
        }

        // Default role if no mapping found
        if (empty($roles)) {
            $roles[] = 'Operator';
        }

        return array_unique($roles);
    }

    /**
     * Get single attribute value from LDAP entry
     */
    private function getAttributeValue(array $entry, string $attribute): ?string
    {
        if (!isset($entry[$attribute])) {
            return null;
        }

        if (!is_array($entry[$attribute])) {
            return $entry[$attribute];
        }

        if ($entry[$attribute]['count'] === 0) {
            return null;
        }

        return $entry[$attribute][0];
    }

    /**
     * Get multiple attribute values from LDAP entry
     */
    private function getAttributeValues(array $entry, string $attribute): array
    {
        if (!isset($entry[$attribute])) {
            return [];
        }

        if (!is_array($entry[$attribute])) {
            return [$entry[$attribute]];
        }

        $values = [];
        for ($i = 0; $i < $entry[$attribute]['count']; $i++) {
            $values[] = $entry[$attribute][$i];
        }

        return $values;
    }

    /**
     * Search LDAP directory
     */
    public function search(string $baseDn, string $filter, array $attributes = []): array
    {
        if ($this->connection === null) {
            $this->connect();
            $this->bind();
        }

        $searchResult = ldap_search($this->connection, $baseDn, $filter, $attributes);
        
        if ($searchResult === false) {
            return [];
        }

        $entries = ldap_get_entries($this->connection, $searchResult);
        
        if ($entries === false) {
            return [];
        }

        // Remove count key
        unset($entries['count']);

        return $entries;
    }

    /**
     * Disconnect from LDAP server
     */
    public function disconnect(): void
    {
        if ($this->connection !== null) {
            ldap_unbind($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Check if LDAP extension is available
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('ldap');
    }

    /**
     * Get last LDAP error
     */
    public function getLastError(): ?string
    {
        if ($this->connection !== null) {
            return ldap_error($this->connection);
        }
        return null;
    }
}
