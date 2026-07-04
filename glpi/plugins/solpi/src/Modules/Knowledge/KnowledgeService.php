<?php
declare(strict_types=1);

namespace SOLPI\Modules\Knowledge;

use SOLPI\Knowledge\KnowledgeEngine;
use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeService
{
    private KnowledgeEngine $engine;
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->engine = new KnowledgeEngine();
        $this->repository = new KnowledgeRepository();
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
     * @param array<string,mixed> $relationship
     * @return bool
     */
    public function linkEntities(string $sourceId, string $targetId, array $relationship): bool
    {
        return $this->engine->addRelationship($sourceId, $targetId, $relationship);
    }

    /**
     * @return array<string,mixed>
     */
    public function getGraphStats(): array
    {
        return $this->repository->getGraphStatistics();
    }
}

