<?php
declare(strict_types=1);

namespace SOLPI\AI;

final class ModelSelector
{
    /**
     * @param array<int,mixed> $arguments
     */
    public function select(string $taskType): string
    {
        return match ($taskType) {
            'complex' => 'gpt-4o',
            'fast'    => 'gpt-4o-mini',
            'local'   => 'llama3',
            default   => 'gpt-4o-mini'
        };
    }
}

