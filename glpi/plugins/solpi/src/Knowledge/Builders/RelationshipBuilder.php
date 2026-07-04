<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Builders;

final class RelationshipBuilder
{
    public function build(string $fromId, string $toId, string $type): \SOLPI\Knowledge\Graph\Edge
    {
        return new \SOLPI\Knowledge\Graph\Edge(
            $fromId,
            $toId,
            $type,
            ['created_at' => date('Y-m-d H:i:s')]
        );
    }
}

