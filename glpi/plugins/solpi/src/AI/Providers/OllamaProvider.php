<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

final class OllamaProvider extends AbstractAIProvider
{
    public function name(): string
    {
        return 'Ollama';
    }

    /**
     * @param array<string,mixed> $context
     */
    public function chat(
        string $prompt,
        array $context=[]
    ): string{

        return '';

    }

    public function embedding(
        string $text
    ): array{

        return [];

    }
}
