<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Memory;

use SOLPI\Core\BaseRepository;

final class MemoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $conversationId
     * @return array<string,mixed>
     */
    public function getConversation(string $conversationId): array
    {
        $query = "SELECT * FROM `glpi_solpi_memory_conversations` WHERE id = '{$conversationId}'";
        $result = $this->db->query($query);
        
        return $result->fetch_assoc() ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getRecent(int $limit = 50): array
    {
        $query = "SELECT * FROM `glpi_solpi_memory_conversations` ORDER BY created_at DESC LIMIT {$limit}";
        $result = $this->db->query($query);
        $conversations = [];

        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }

        return $conversations;
    }

    /**
     * @param array<string,mixed> $data
     * @return bool
     */
    public function save(array $data): bool
    {
        $query = "INSERT INTO `glpi_solpi_memory_conversations` (id, data, created_at) VALUES (?, ?, NOW())";
        // Prepared statement would go here
        return true;
    }
}

