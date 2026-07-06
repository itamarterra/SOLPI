<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\AI\Providers\ProviderFactory;

/**
 * Motor de Análise de Causa Raiz (RCA) via Inteligência de Grafo.
 */
final class RootCauseAnalysisService
{
    private DatabaseManager $db;
    private ProviderFactory $ai;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->ai = new ProviderFactory();
    }

    /**
     * Analisa falhas em cascata e identifica o culpado provável.
     */
    public function analyzeFailures(): array
    {
        $nodes = $this->db->table('glpi_plugin_solpi_inframap_nodes')->get();
        $offlineNodes = [];

        foreach ($nodes as $node) {
            $meta = json_decode((string)$node['metadata'], true) ?: [];
            if (($meta['status'] ?? '') === 'OFFLINE') {
                $offlineNodes[] = [
                    'label' => $node['label'],
                    'uuid'  => $node['uuid'],
                    'class' => $node['class']
                ];
            }
        }

        if (count($offlineNodes) < 2) {
            return []; // RCA só faz sentido para falhas múltiplas
        }

        // Busca relacionamentos entre os ativos offline
        $context = $this->buildGraphContext($offlineNodes);

        $prompt = "Abaixo está um conjunto de ativos de TI que ficaram OFFLINE simultaneamente.
                   Considere os relacionamentos do Grafo de Infraestrutura:
                   {$context}

                   Analise a topologia e identifique qual é a Causa Raiz provável.
                   Explique de forma técnica e executiva em no máximo 3 frases.";

        $explanation = $this->ai->createDefault()->chat($prompt);

        return [
            'explanation' => $explanation,
            'affected_count' => count($offlineNodes),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function buildGraphContext(array $offlineNodes): string
    {
        $uuids = array_column($offlineNodes, 'uuid');
        $edges = $this->db->table('glpi_plugin_solpi_inframap_edges')
                 ->where(['source_uuid' => $uuids])
                 ->get();

        $text = "Ativos afetados: " . implode(', ', array_column($offlineNodes, 'label')) . ".\n";
        $text .= "Conexões detectadas no Twin:\n";
        foreach ($edges as $edge) {
            $text .= "- {$edge['source_uuid']} está conectado a {$edge['target_uuid']} via {$edge['relation_type']}.\n";
        }

        return $text;
    }
}
