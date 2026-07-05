<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Modules\Intelligence\Repositories\GraphRepository;

/**
 * Motor de navegação profunda no Incident Graph.
 * Responsável por percorrer as cadeias de dependências para encontrar a origem e o impacto.
 */
final class TopologyTraverser
{
    private GraphRepository $repository;

    public function __construct()
    {
        $this->repository = new GraphRepository();
    }

    /**
     * Busca a origem provável subindo na árvore de dependências (Upstream)
     *
     * @param string $startNodeId ID do nó inicial (ex: "Ticket:150" ou "Computer:40")
     * @param int $maxDepth Profundidade máxima da busca
     * @return array<int, array> Lista de nós que sustentam o nó inicial
     */
    public function traceUpstream(string $startNodeId, int $maxDepth = 5): array
    {
        $visited = [];
        return $this->traverse($startNodeId, 'DEPENDS_ON', $maxDepth, $visited);
    }

    /**
     * Busca o impacto total descendo na árvore de dependências (Downstream)
     *
     * @param string $startNodeId ID do nó inicial (ex: "ZabbixAlert:10")
     * @param int $maxDepth Profundidade máxima da busca
     * @return array<int, array> Lista de nós afetados pelo nó inicial
     */
    public function traceDownstream(string $startNodeId, int $maxDepth = 5): array
    {
        $visited = [];
        return $this->traverse($startNodeId, 'SUPPORTS', $maxDepth, $visited);
    }

    /**
     * Algoritmo genérico de travessia de grafo (DFS simplificado)
     */
    private function traverse(string $nodeId, string $relationType, int $depth, array &$visited): array
    {
        if ($depth <= 0 || in_array($nodeId, $visited)) {
            return [];
        }

        $visited[] = $nodeId;
        $results = [];

        // Busca conexões que partem ou chegam neste nó com a relação específica
        // Para UPSTREAM, buscamos por quem este nó DEPENDS_ON
        // Para DOWNSTREAM, buscamos quem este nó SUPPORTS
        $edges = $this->repository->getNeighbors($nodeId);

        foreach ($edges as $edge) {
            if ($edge['relation'] === $relationType) {
                $targetId = $edge['target_canonical_id'];
                $results[] = [
                    'id' => $targetId,
                    'depth' => $depth,
                    'relation' => $relationType
                ];

                // Recursão para o próximo nível
                $results = array_merge($results, $this->traverse($targetId, $relationType, $depth - 1, $visited));
            }
        }

        return $results;
    }

    /**
     * Localiza todos os alertas ativos na vizinhança de um nó
     */
    public function findActiveAlertsInNeighborhood(string $nodeId): array
    {
        $alerts = [];
        $neighbors = $this->repository->getInboundEdges($nodeId);

        foreach ($neighbors as $edge) {
            if (str_starts_with($edge['source_canonical_id'], 'Alert:')) {
                $alerts[] = $edge['source_canonical_id'];
            }
        }

        return $alerts;
    }
}
