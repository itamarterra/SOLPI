<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Repositories;

use DBmysql;
use RuntimeException;

final class KnowledgeRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Banco de dados do GLPI não disponível.'
            );
        }

        $this->db = $DB;
    }

    public function saveEntity(
        string $type,
        string $uuid,
        array $data
    ): int {

        $this->db->insert(
            'glpi_plugin_solpi_knowledge_entities',
            [
                'entity_type' => $type,
                'entity_uuid' => $uuid,
                'payload' => json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                ),
                'created_at' => date('Y-m-d H:i:s')
            ]
        );

        return (int)$this->db->insertId();
    }

    public function entities(): array
    {
        return iterator_to_array(
            $this->db->request([
                'FROM' => 'glpi_plugin_solpi_knowledge_entities'
            ])
        );
    }
}
