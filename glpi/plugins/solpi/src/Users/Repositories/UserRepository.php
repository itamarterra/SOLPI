<?php

declare(strict_types=1);

namespace SOLPI\Users\Repositories;

use DBmysql;
use RuntimeException;
use SOLPI\Users\Entities\User;

final class UserRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Conexão com o banco do GLPI não encontrada.'
            );
        }

        $this->db = $DB;
    }

    public function create(User $user): int
    {
        $this->db->insert(
            'glpi_plugin_solpi_users',
            [
                'uuid'        => $user->uuid(),
                'name'        => $user->name(),
                'email'       => $user->email(),
                'phone'       => $user->phone(),
                'department'  => $user->department(),
                'position'    => $user->position(),
                'company_id'  => $user->companyId(),
                'active'      => $user->active() ? 1 : 0,
                'settings'    => json_encode(
                    $user->settings(),
                    JSON_UNESCAPED_UNICODE
                ),
                'created_at'  => $user->createdAt()->format('Y-m-d H:i:s'),
                'updated_at'  => $user->updatedAt()->format('Y-m-d H:i:s')
            ]
        );

        return (int)$this->db->insertId();
    }

    public function update(
        int $id,
        User $user
    ): bool {

        return (bool)$this->db->update(
            'glpi_plugin_solpi_users',
            [
                'name'        => $user->name(),
                'email'       => $user->email(),
                'phone'       => $user->phone(),
                'department'  => $user->department(),
                'position'    => $user->position(),
                'company_id'  => $user->companyId(),
                'active'      => $user->active() ? 1 : 0,
                'settings'    => json_encode(
                    $user->settings(),
                    JSON_UNESCAPED_UNICODE
                ),
                'updated_at'  => date('Y-m-d H:i:s')
            ],
            [
                'id' => $id
            ]
        );
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->delete(
            'glpi_plugin_solpi_users',
            [
                'id' => $id
            ]
        );
    }

    public function find(int $id): ?array
    {
        $iterator = $this->db->request([
            'FROM' => 'glpi_plugin_solpi_users',
            'WHERE' => [
                'id' => $id
            ]
        ]);

        foreach ($iterator as $row) {
            return $row;
        }

        return null;
    }

    public function findByUUID(string $uuid): ?array
    {
        $iterator = $this->db->request([
            'FROM' => 'glpi_plugin_solpi_users',
            'WHERE' => [
                'uuid' => $uuid
            ]
        ]);

        foreach ($iterator as $row) {
            return $row;
        }

        return null;
    }

    public function all(): array
    {
        return iterator_to_array(
            $this->db->request([
                'FROM' => 'glpi_plugin_solpi_users',
                'ORDER' => 'name ASC'
            ])
        );
    }

    public function count(): int
    {
        return count($this->all());
    }
}
