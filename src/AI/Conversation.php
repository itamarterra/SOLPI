<?php
declare(strict_types=1);

namespace SOLPI\AI;

final class Conversation
{
    /**
     * @param array<int,mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return null;
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}

