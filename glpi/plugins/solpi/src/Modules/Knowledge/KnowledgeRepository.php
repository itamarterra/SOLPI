<?php
declare(strict_types=1);

namespace SOLPI\Modules\Knowledge;

use SOLPI\Core\BaseRepository;
use SOLPI\Core\Database\QueryBuilder;

final class KnowledgeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array<string,mixed>
     */
    public function getGraphStatistics(): array
    {
        $qb = new QueryBuilder($this->db);
        
        $nodes = $qb->from('glpi_solpi_knowledge_nodes')
                    ->select(['COUNT(*) as nodes'])
                    ->first();
                    
        $edges = (new QueryBuilder($this->db))->from('glpi_solpi_knowledge_edges')
                    ->select(['COUNT(*) as edges'])
                    ->first();

        return [
            'nodes' => (int)($nodes['nodes'] ?? 0),
            'edges' => (int)($edges['edges'] ?? 0),
        ];
    }

    /**
     * @param array<string,mixed> $entityData
     * @return array<string,mixed>
     */
    public function insertEntity(array $entityData): array
    {
        $this->db->insert('glpi_solpi_knowledge_nodes', $entityData);
        return $entityData;
    }

    /**
     * @param string $sourceId
     * @param string $targetId
     * @param array<string,mixed> $relationshipData
     * @return bool
     */
    public function insertRelationship(string $sourceId, string $targetId, array $relationshipData): bool
    {
        $data = array_merge($relationshipData, [
            'source_id' => $sourceId,
            'target_id' => $targetId
        ]);
        
        return $this->db->insert('glpi_solpi_knowledge_edges', $data);
    }
}

