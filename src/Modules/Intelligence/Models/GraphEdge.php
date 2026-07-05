<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Models;

/**
 * Representação de uma conexão (aresta) no Incident Graph
 */
final class GraphEdge
{
    public string $sourceId;
    public string $targetId;
    public string $relation;
    public float $weight;
    public array $properties;

    public function __construct(string $sourceId, string $targetId, string $relation, float $weight = 1.0, array $properties = [])
    {
        $this->sourceId = $sourceId;
        $this->targetId = $targetId;
        $this->relation = $relation;
        $this->weight = $weight;
        $this->properties = $properties;
    }

    public function toArray(): array
    {
        return [
            'from' => $this->sourceId,
            'to' => $this->targetId,
            'type' => $this->relation,
            'weight' => $this->weight,
            'metadata' => $this->properties
        ];
    }
}
