<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Models;

/**
 * Representação de uma entidade no Incident Graph
 */
final class GraphNode
{
    public string $canonicalId;
    public string $type;
    public string $label;
    public array $properties;

    public function __construct(string $canonicalId, string $type, string $label = '', array $properties = [])
    {
        $this->canonicalId = $canonicalId;
        $this->type = $type;
        $this->label = $label ?: $canonicalId;
        $this->properties = $properties;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->canonicalId,
            'type' => $this->type,
            'label' => $this->label,
            'properties' => $this->properties
        ];
    }
}
