<?php

declare(strict_types=1);

namespace SOLPI\Agent\Registry;

use SOLPI\Core\BaseRepository;

final class InstallationRepository extends BaseRepository
{
    protected string $table = 'glpi_plugins_solpi_installations';

    public function createFromArray(array $data): int
    {
        $payload = [
            'site_name' => $data['site_name'] ?? 'unknown',
            'site_url' => $data['site_url'] ?? null,
            'glpi_version' => $data['glpi_version'] ?? null,
            'solpi_version' => $data['solpi_version'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'capabilities' => isset($data['capabilities']) ? json_encode($data['capabilities']) : null,
            'inventory' => isset($data['inventory']) ? json_encode($data['inventory']) : null,
            'status' => $data['status'] ?? 'online',
            'last_seen' => $data['last_seen'] ?? date('Y-m-d H:i:s'),
            'auth_token' => $data['auth_token'] ?? null,
        ];

        return $this->insert($payload);
    }

    public function updateFromArray(int $id, array $data): bool
    {
        $payload = [];

        if (isset($data['site_name'])) { $payload['site_name']=$data['site_name']; }
        if (isset($data['site_url'])) { $payload['site_url']=$data['site_url']; }
        if (isset($data['glpi_version'])) { $payload['glpi_version']=$data['glpi_version']; }
        if (isset($data['solpi_version'])) { $payload['solpi_version']=$data['solpi_version']; }
        if (isset($data['ip_address'])) { $payload['ip_address']=$data['ip_address']; }
        if (isset($data['capabilities'])) { $payload['capabilities']=json_encode($data['capabilities']); }
        if (isset($data['inventory'])) { $payload['inventory']=json_encode($data['inventory']); }
        if (isset($data['status'])) { $payload['status']=$data['status']; }
        if (isset($data['last_seen'])) { $payload['last_seen']=$data['last_seen']; }
        if (isset($data['auth_token'])) { $payload['auth_token']=$data['auth_token']; }

        if (empty($payload)) {
            return false;
        }

        return $this->update($id, $payload);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listAll(int $limit = 100, int $offset = 0): array
    {
        $all = $this->all();

        return array_slice($all, $offset, $limit);
    }

    public function listPending(int $limit = 100, int $offset = 0): array
    {
        $rows = [];

        foreach ($this->db->request([
            'FROM' => $this->table,
            'WHERE' => [ 'approved' => 0 ],
            'ORDER' => 'created_at DESC',
            'LIMIT' => $limit
        ]) as $r) {
            $rows[] = $r;
        }

        return $rows;
    }

    public function setApproved(int $id, bool $approved, ?string $by = null): bool
    {
        $payload = [
            'approved' => $approved ? 1 : 0,
            'approved_by' => $by,
            'approved_at' => $approved ? date('Y-m-d H:i:s') : null,
        ];

        return $this->update($id, $payload);
    }

    public function revokeToken(int $id, ?string $by = null): bool
    {
        $payload = [
            'token_revoked' => 1,
            'token_revoked_at' => date('Y-m-d H:i:s'),
            'auth_token' => null,
        ];

        if ($by !== null) { $payload['approved_by'] = $by; }

        return $this->update($id, $payload);
    }

    /**
     * Rotate the token for an installation. Returns the hashed token stored.
     */
    public function rotateToken(int $id, string $tokenHash): bool
    {
        $payload = [
            'auth_token' => $tokenHash,
            'token_revoked' => 0,
            'token_last_rotated' => date('Y-m-d H:i:s'),
        ];

        return $this->update($id, $payload);
    }
}
