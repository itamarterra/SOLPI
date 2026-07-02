<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class LdapAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!function_exists('ldap_connect')) {
            throw new RuntimeException('LDAP extension not available in runtime.');
        }

        $host = (string)($payload['host'] ?? '');
        $baseDn = (string)($payload['base_dn'] ?? '');
        $filter = (string)($payload['filter'] ?? '(objectClass=*)');
        $port = (int)($payload['port'] ?? 389);

        if ($host === '' || $baseDn === '') {
            throw new RuntimeException('LDAP adapter requires host and base_dn.');
        }

        $conn = ldap_connect($host, $port);
        if ($conn === false) {
            throw new RuntimeException('LDAP connection failed.');
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bindDn = (string)($payload['bind_dn'] ?? '');
        $bindPassword = (string)($payload['bind_password'] ?? '');
        $bind = $bindDn !== '' ? @ldap_bind($conn, $bindDn, $bindPassword) : @ldap_bind($conn);
        if ($bind === false) {
            throw new RuntimeException('LDAP bind failed.');
        }

        $attrs = is_array($payload['attributes'] ?? null) ? $payload['attributes'] : [];
        $search = @ldap_search($conn, $baseDn, $filter, $attrs ?: null);
        if ($search === false) {
            throw new RuntimeException('LDAP search failed.');
        }

        $entries = ldap_get_entries($conn, $search);
        $records = [];
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if (!is_array($entry) || !isset($entry['dn'])) {
                    continue;
                }
                $records[] = $entry;
            }
        }

        ldap_unbind($conn);

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'ldap',
                'count' => count($records),
            ],
        ];
    }
}
