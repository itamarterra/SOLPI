<?php
declare(strict_types=1);

namespace SOLPI\Modules\Settings;

use DBmysql;
use RuntimeException;

final class SettingsRepository
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

    /**
     * @return array<string,mixed>
     */
    public function all(string $module = 'core'): array
    {
        $data = [];

        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_settings',
            'WHERE' => ['module' => $module],
            'ORDER' => 'key ASC',
        ]) as $row) {
            $key = (string)($row['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $data[$key] = $this->decodeValue(
                $row['value'] ?? null,
                (string)($row['type'] ?? 'string')
            );
        }

        return $data;
    }

    public function get(string $module, string $key, mixed $default = null): mixed
    {
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_settings',
            'WHERE' => [
                'module' => $module,
                'key' => $key,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            return $this->decodeValue(
                $row['value'] ?? null,
                (string)($row['type'] ?? 'string')
            );
        }

        return $default;
    }

    public function upsert(string $module, string $key, mixed $value, string $type = 'string'): void
    {
        $encoded = $this->encodeValue($value, $type);

        $existingId = null;
        foreach ($this->db->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_solpi_settings',
            'WHERE' => [
                'module' => $module,
                'key' => $key,
            ],
            'LIMIT' => 1,
        ]) as $row) {
            $existingId = (int)$row['id'];
        }

        if ($existingId !== null) {
            $this->db->update('glpi_plugin_solpi_settings', [
                'value' => $encoded,
                'type' => $type,
            ], [
                'id' => $existingId,
            ]);

            return;
        }

        $this->db->insert('glpi_plugin_solpi_settings', [
            'module' => $module,
            'key' => $key,
            'value' => $encoded,
            'type' => $type,
        ]);
    }

    private function encodeValue(mixed $value, string $type): string
    {
        if ($type === 'json') {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: 'null';
        }

        if ($type === 'bool') {
            return $value ? '1' : '0';
        }

        return (string)$value;
    }

    private function decodeValue(mixed $value, string $type): mixed
    {
        if ($type === 'json') {
            $decoded = json_decode((string)$value, true);
            return is_array($decoded) ? $decoded : null;
        }

        if ($type === 'int') {
            return (int)$value;
        }

        if ($type === 'float') {
            return (float)$value;
        }

        if ($type === 'bool') {
            return (string)$value === '1';
        }

        return (string)$value;
    }
}

