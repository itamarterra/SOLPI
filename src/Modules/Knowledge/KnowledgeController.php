<?php
declare(strict_types=1);

namespace SOLPI\Modules\Knowledge;

use SOLPI\Knowledge\KnowledgeEngine;
use SOLPI\Knowledge\Services\SemanticSearch;

final class KnowledgeController
{
    private KnowledgeEngine $engine;
    private SemanticSearch $search;

    public function __construct()
    {
        $this->engine = new KnowledgeEngine();
        $this->search = new SemanticSearch();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function search(string $query, array $filters = []): array
    {
        return $this->search->execute($query, $filters);
    }

    /**
     * @param array<string,mixed> $metadata
     * @return array<string,mixed>
     */
    public function addEntity(string $type, array $metadata): array
    {
        return $this->engine->addEntity($type, $metadata);
    }

    /**
     * @return array<string,mixed>
     */
    public function graph(): array
    {
        return $this->engine->getGraph();
    }
}

