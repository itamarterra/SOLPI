<?php

declare(strict_types=1);

namespace SOLPI\AI;

use SOLPI\Core\Database;

final class AIRepository
{
    public function __construct(
        private Database $database
    ) {
    }

    public function saveConversation(
        array $data
    ): int {

        $db = $this->database->connection();

        $db->insert(
            'glpi_plugin_solpi_ai_conversations',
            $data
        );

        return (int)$db->insertId();
    }

    public function saveMessage(
        array $data
    ): int {

        $db = $this->database->connection();

        $db->insert(
            'glpi_plugin_solpi_ai_messages',
            $data
        );

        return (int)$db->insertId();
    }

    public function saveMemory(
        array $data
    ): int {

        $db = $this->database->connection();

        $db->insert(
            'glpi_plugin_solpi_ai_memory',
            $data
        );

        return (int)$db->insertId();
    }

    public function saveEntity(
        array $data
    ): int {

        $db = $this->database->connection();

        $db->insert(
            'glpi_plugin_solpi_ai_entities',
            $data
        );

        return (int)$db->insertId();
    }

    public function saveRelationship(
        array $data
    ): int {

        $db = $this->database->connection();

        $db->insert(
            'glpi_plugin_solpi_ai_relationships',
            $data
        );

        return (int)$db->insertId();
    }
}