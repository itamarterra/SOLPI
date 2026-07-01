<?php
declare(strict_types=1);

namespace SOLPI\AI\Providers;

final class GeminiProvider extends AbstractAIProvider
{
    public function name(): string
    {
        return 'Gemini';
    }

    /**
     * @param array<string,mixed> $context
     */
    public function chat(string $prompt, array $context = []): string
    {
        return '';
    }

    /**
     * @return array<int,float>
     */
    public function embedding(string $text): array
    {
        return [];
    }
}

