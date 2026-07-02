<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\KnowledgeGraphRepository;

final class KnowledgeGraphProjector
{
    private KnowledgeGraphRepository $graph;

    public function __construct()
    {
        $this->graph = new KnowledgeGraphRepository();
    }

    /**
     * @param array<string,mixed> $record
     */
    public function project(string $entityType, string $canonicalId, ?int $entityId, array $record): void
    {
        $label = (string)($record['name'] ?? $record['hostname'] ?? $record['email'] ?? $canonicalId);

        $this->graph->upsertNode($canonicalId, $entityType, $entityId, $label, [
            'source' => $record['source'] ?? 'integration_engine',
            'updated_at' => date(DATE_ATOM),
        ]);

        $companyCanonical = isset($record['company_canonical_id']) ? (string)$record['company_canonical_id'] : '';
        if ($companyCanonical !== '' && $entityType !== 'company') {
            $this->graph->upsertEdge(
                $companyCanonical,
                $canonicalId,
                'contains',
                1.0,
                ['entity_type' => $entityType]
            );
        }
    }
}
