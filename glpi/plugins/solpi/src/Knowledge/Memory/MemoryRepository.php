<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Memory;

use SOLPI\Core\BaseRepository;
use SOLPI\Core\Database\QueryBuilder;

final class MemoryRepository extends BaseRepository
{
    protected string $table = 'glpi_solpi_memory_conversations';

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
        $qb = new QueryBuilder($this->db);
        $result = $qb->from($this->table)
                    ->where(['id' => $conversationId])
                    ->first();
        
        return $result ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getRecent(int $limit = 50): array
    {
        $qb = new QueryBuilder($this->db);
        return $qb->from($this->table)
                  ->orderBy('created_at', 'DESC')
                  ->limit($limit)
                  ->execute();
    }

    /**
     * @param array<string,mixed> $data
     * @return bool
     */
    public function save(array $data): bool
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data) > 0;
    }
}

