<?php
declare(strict_types=1);

namespace SOLPI\AI;

final class Conversation
{
    private array $metadata;

    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

