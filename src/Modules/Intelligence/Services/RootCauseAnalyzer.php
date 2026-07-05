<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

/**
 * Orquestrador central da análise de incidentes.
 * Consolida lógica heurística, semântica e topológica.
 */
final class RootCauseAnalyzer
{
    private HeuristicEngine $heuristics;
    private SimilarityService $similarity;

    public function __construct()
    {
        $this->heuristics = new HeuristicEngine();
        $this->similarity = new SimilarityService();
    }

    /**
     * Realiza uma análise completa de um incidente/chamado
     */
    public function analyze(string $targetId): array
    {
        // 1. Tenta dedução técnica via Heurística (Topologia)
        $technicalResult = $this->heuristics->analyzeRootCause($targetId);

        // 2. Tenta dedução semântica via Histórico (Similaridade)
        // (A ser implementado no Módulo 3 com IA)

        // 3. Calcula o Impacto
        $impactScore = $this->heuristics->calculateImpactScore($technicalResult['cause_id'] ?? $targetId);

        return [
            'target' => $targetId,
            'root_cause' => [
                'id' => $technicalResult['cause_id'],
                'confidence' => $technicalResult['confidence'],
                'reason' => $technicalResult['reasoning']
            ],
            'impact' => [
                'score' => $impactScore,
                'level' => $this->mapImpactLevel($impactScore)
            ],
            'analyzed_at' => date('Y-m-d H:i:s')
        ];
    }

    private function mapImpactLevel(float $score): string
    {
        if ($score >= 0.8) return 'CRÍTICO';
        if ($score >= 0.5) return 'ALTO';
        if ($score >= 0.3) return 'MÉDIO';
        return 'BAIXO';
    }
}
