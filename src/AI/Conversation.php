<?php

declare(strict_types=1);

namespace SOLPI\AI;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Helpers\DateHelper;

/**
 * Gerenciador de conversas e histórico para a IA
 */
final class Conversation
{
    private int $id;
    private array $history = [];
    private string $context = '';

    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    public function addMessage(string $role, string $content): void
    {
        $this->history[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => DateHelper::now()
        ];
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function save(): bool
    {
        if ($this->id === 0) {
            return false;
        }

        $db = DatabaseManager::getInstance()->getConnection();
        return (bool)$db->insert('glpi_plugin_solpi_conversations', [
            'tickets_id' => $this->id,
            'payload'    => json_encode($this->history, JSON_UNESCAPED_UNICODE),
            'date_mod'   => DateHelper::now()
        ]);
    }

    public static function loadForTicket(int $ticketId): self
    {
        $instance = new self($ticketId);
        $db = DatabaseManager::getInstance();
        $row = $db->table('glpi_plugin_solpi_conversations')
            ->where(['tickets_id' => $ticketId])
            ->first();

        if ($row && isset($row['payload'])) {
            $instance->history = json_decode($row['payload'], true) ?: [];
        }

        return $instance;
    }
}
