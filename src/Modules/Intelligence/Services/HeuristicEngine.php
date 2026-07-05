<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

/**
 * Motor de Lógica Heurística para dedução de Causa Raiz.
 * Aplica regras de infraestrutura baseadas em topologia e severidade.
 */
final class HeuristicEngine
{
    private TopologyTraverser $traverser;

    public function __construct()
    {
        $this->traverser = new TopologyTraverser();
    }

    /**
     * Analisa um nó problemático e tenta deduzir a causa raiz
     *
     * @return array{cause_id:string|null, confidence:float, reasoning:string}
     */
    public function analyzeRootCause(string $targetId): array
    {
        // 1. Busca alertas diretos no próprio nó
        $directAlerts = $this->traverser->findActiveAlertsInNeighborhood($targetId);
        if (!empty($directAlerts)) {
            return [
                'cause_id'   => $targetId,
                'confidence' => 0.90,
                'reasoning'  => "Alerta crítico detectado diretamente no ativo."
            ];
        }

        // 2. Sobe na topologia em busca de falhas em níveis superiores (Upstream)
        $upstreamNodes = $this->traverser->traceUpstream($targetId, 3);

        foreach ($upstreamNodes as $node) {
            $alerts = $this->traverser->findActiveAlertsInNeighborhood($node['id']);
            if (!empty($alerts)) {
                // Se encontramos um alerta em um nível superior, ele é o forte candidato
                return [
                    'cause_id'   => $node['id'],
                    'confidence' => 0.85,
                    'reasoning'  => "Falha detectada em ativo de nível superior ({$node['id']}) que sustenta o item atual."
                ];
            }
        }

        // 3. Regra de Rede: Verifica se há falhas em switches/roteadores próximos
        // (Será expandido conforme o CMDB for populado)

        return [
            'cause_id'   => null,
            'confidence' => 0.0,
            'reasoning'  => "Nenhuma causa raiz técnica óbvia detectada via topologia."
        ];
    }

    /**
     * Calcula a pontuação de impacto baseada na profundidade da árvore afetada
     */
    public function calculateImpactScore(string $nodeId): float
    {
        $affected = $this->traverser->traceDownstream($nodeId, 5);
        $count = count($affected);

        if ($count === 0) return 0.1;
        if ($count < 5) return 0.4;
        if ($count < 20) return 0.7;

        return 1.0; // Impacto Massivo
    }
}
