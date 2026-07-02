<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class UserRecordRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;
        if (!$DB instanceof DBmysql) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }
        $this->db = $DB;
    }

    public function findCandidate(array $record): ?array
    {
        $uuid = (string)($record['uuid'] ?? '');
        $email = mb_strtolower(trim((string)($record['email'] ?? '')));
        $cpf = preg_replace('/\D+/', '', (string)($record['cpf'] ?? $record['document'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($record['phone'] ?? ''));

        foreach ([
            $uuid !== '' ? ['uuid' => $uuid] : null,
            $email !== '' ? ['email' => $email] : null,
            $phone !== '' ? ['phone' => $phone] : null,
            $cpf !== '' ? ['settings' => '%"cpf":"' . $cpf . '"%'] : null,
        ] as $where) {
            if (!is_array($where)) {
                continue;
            }

            $found = $this->findBy($where);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    public function upsert(array $record): array
    {
        $candidate = $this->findCandidate($record);

        $settings = $record['settings'] ?? [];
        if (!is_array($settings)) {
            $settings = [];
        }

        $cpf = preg_replace('/\D+/', '', (string)($record['cpf'] ?? $record['document'] ?? ''));
        if ($cpf !== '') {
            $settings['cpf'] = $cpf;
        }

        $data = [
            'uuid' => (string)($record['uuid'] ?? hash('sha256', json_encode($record))),
            'name' => (string)($record['name'] ?? 'Usuario sem nome'),
            'email' => isset($record['email']) ? mb_strtolower(trim((string)$record['email'])) : null,
            'phone' => preg_replace('/\D+/', '', (string)($record['phone'] ?? '')),
            'department' => $record['department'] ?? null,
            'position' => $record['position'] ?? ($record['cargo'] ?? null),
            'company_id' => isset($record['company_id']) ? (int)$record['company_id'] : null,
            'active' => 1,
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
        ];

        if ($candidate !== null) {
            $id = (int)$candidate['id'];
            $this->db->update('glpi_plugin_solpi_users', $data, ['id' => $id]);
            return ['id' => $id, 'action' => 'updated'];
        }

        $this->db->insert('glpi_plugin_solpi_users', $data);
        return ['id' => (int)$this->db->insertId(), 'action' => 'created'];
    }

    private function findBy(array $where): ?array
    {
        if (isset($where['settings'])) {
            $pattern = (string)$where['settings'];
            $sql = 'SELECT * FROM glpi_plugin_solpi_users WHERE settings LIKE "' . $this->db->escape($pattern) . '" LIMIT 1';
            $iterator = $this->db->query($sql);
            if ($iterator) {
                while ($row = $iterator->next()) {
                    return $row;
                }
            }
            return null;
        }

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_users',
            'WHERE' => $where,
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }

        return null;
    }
}
