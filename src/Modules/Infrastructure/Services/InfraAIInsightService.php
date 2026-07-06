<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\AI\Providers\ProviderFactory;
use SOLPI\Modules\DigitalTwin\Services\SnapshotService;
use SOLPI\Modules\DigitalTwin\Services\ChangeDetectionEngine;

/**
 * Gera insights executivos e proativos sobre a infraestrutura.
 */
final class InfraAIInsightService
{
    private ProviderFactory $ai;
    private SnapshotService $snapshots;
    private ChangeDetectionEngine $diffEngine;

    public function __construct()
    {
        $this->ai = new ProviderFactory();
        $this->snapshots = new SnapshotService();
        $this->diffEngine = new ChangeDetectionEngine();
    }

    /**
     * Gera um resumo executivo das mudanças recentes e análise de causa raiz.
     */
    public function generateExecutiveSummary(): string
    {
        $latest = $this->snapshots->getLatest();

        // Adiciona análise de causa raiz se houver falhas
        $rca = new \SOLPI\Modules\Intelligence\Services\RootCauseAnalysisService();
        $analysis = $rca->analyzeFailures();

        $rcaText = !empty($analysis) ? "\n\n🔍 *ANÁLISE DE CAUSA RAIZ:*\n" . $analysis['explanation'] : "";

        $stats = [
            'total' => $latest ? count($latest->data()['nodes'] ?? []) : 0,
            'changes' => 'Varredura de infraestrutura operacional.'
        ];

        $prompt = "Você é o SOLPI Intelligence. Gere um relatório executivo para o Diretor.
                   Status: {$stats['total']} ativos. {$stats['changes']}
                   Análise adicional: {$rcaText}
                   Seja direto, técnico e profissional.";

        return $this->ai->createDefault()->chat($prompt);
    }
}
