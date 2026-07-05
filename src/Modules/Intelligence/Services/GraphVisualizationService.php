<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Modules\Intelligence\Repositories\GraphRepository;
use SOLPI\Core\Database\DatabaseManager;

/**
 * Prepara os dados do grafo para exibição no Frontend (Vis.js format)
 */
final class GraphVisualizationService
{
    private GraphRepository $repository;
    private DatabaseManager $db;

    public function __construct()
    {
        $this->repository = new GraphRepository();
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Gera o dataset formatado para o Vis.js para um Ticket específico
     */
    public function getGraphDataForTicket(int $ticketId): array
    {
        $startNode = "Ticket:" . $ticketId;
        $nodes = [];
        $edges = [];
        $processedNodes = [];

        $this->expandGraph($startNode, $nodes, $edges, $processedNodes, 2);

        return [
            'nodes' => array_values($nodes),
            'edges' => $edges
        ];
    }

    /**
     * Expande o grafo recursivamente até um limite de profundidade
     */
    private function expandGraph(string $id, array &$nodes, array &$edges, array &$processed, int $depth): void
    {
        if ($depth < 0 || in_array($id, $processed)) {
            return;
        }

        $processed[] = $id;

        // 1. Busca o nó no banco
        $nodeData = $this->db->table('glpi_plugin_solpi_kg_nodes')
            ->where(['canonical_id' => $id])
            ->first();

        if ($nodeData) {
            $nodes[$id] = [
                'id'    => $id,
                'label' => $nodeData['label'],
                'group' => $nodeData['entity_type'],
                'title' => $nodeData['properties']
            ];
        }

        // 2. Busca conexões Saintes
        $outbound = $this->repository->getNeighbors($id);
        foreach ($outbound as $edge) {
            $target = $edge['target_canonical_id'];
            $edges[] = [
                'from'  => $id,
                'to'    => $target,
                'label' => $edge['relation'],
                'arrows' => 'to'
            ];
            $this->expandGraph($target, $nodes, $edges, $processed, $depth - 1);
        }

        // 3. Busca conexões Entrantes
        $inbound = $this->repository->getInboundEdges($id);
        foreach ($inbound as $edge) {
            $source = $edge['source_canonical_id'];
            $edges[] = [
                'from'  => $source,
                'to'    => $id,
                'label' => $edge['relation'],
                'arrows' => 'to'
            ];
            $this->expandGraph($source, $nodes, $edges, $processed, $depth - 1);
        }
    }
}
