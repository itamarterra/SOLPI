<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use RuntimeException;

final class CompanyRecordRepository
{
    private object $db;

    public function __construct()
    {
        global $DB;
        if (!is_object($DB)) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }
        $this->db = $DB;
    }

    public function findCandidate(array $record): ?array
    {
        $uuid = (string)($record['uuid'] ?? $record['solpi_uuid'] ?? '');
        $document = preg_replace('/\D+/', '', (string)($record['cnpj'] ?? $record['document'] ?? ''));
        $email = mb_strtolower(trim((string)($record['email'] ?? '')));
        $name = trim((string)($record['name'] ?? ''));

        if ($uuid !== '') {
            $found = $this->findBy(['uuid' => $uuid]);
            if ($found !== null) {
                return $found;
            }
        }

        if ($document !== '') {
            $found = $this->findBy(['document' => $document]);
            if ($found !== null) {
                return $found;
            }
        }

        if ($email !== '') {
            $found = $this->findBy(['email' => $email]);
            if ($found !== null) {
                return $found;
            }
        }

        if ($name !== '') {
            $found = $this->findBy(['name' => $name]);
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
        $data = [
            'uuid' => (string)($record['uuid'] ?? $record['solpi_uuid'] ?? hash('sha256', json_encode($record))),
            'name' => (string)($record['name'] ?? 'Empresa sem nome'),
            'trade_name' => $record['trade_name'] ?? null,
            'document' => preg_replace('/\D+/', '', (string)($record['cnpj'] ?? $record['document'] ?? '')),
            'email' => isset($record['email']) ? mb_strtolower(trim((string)$record['email'])) : null,
            'phone' => $record['phone'] ?? null,
            'website' => $record['website'] ?? ($record['domain'] ?? null),
            'address' => $record['address'] ?? null,
            'city' => $record['city'] ?? null,
            'state' => $record['state'] ?? null,
            'zip_code' => $record['zip_code'] ?? ($record['cep'] ?? null),
            'active' => 1,
            'settings' => json_encode($record['settings'] ?? [], JSON_UNESCAPED_UNICODE),
            'metadata' => json_encode($record['metadata'] ?? $record, JSON_UNESCAPED_UNICODE),
        ];

        if ($candidate !== null) {
            $id = (int)$candidate['id'];
            $this->db->update('glpi_plugin_solpi_companies', $data, ['id' => $id]);
            return ['id' => $id, 'action' => 'updated'];
        }

        $this->db->insert('glpi_plugin_solpi_companies', $data);
        return ['id' => (int)$this->db->insertId(), 'action' => 'created'];
    }

    private function findBy(array $where): ?array
    {
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_companies',
            'WHERE' => $where,
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }

        return null;
    }
}
