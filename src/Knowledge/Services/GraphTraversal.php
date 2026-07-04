<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class GraphTraversal
{
    public function __construct()
    {
        // Relationship repository traversal is pending implementation.
    }

    public function neighbours(
        string $uuid
    ): array{

        unset($uuid);

        // Relationship graph persistence is not implemented yet.
        return [];

    }
}
