<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

interface AIProviderInterface
{
    public function name(): string;

    public function chat(
        string $prompt,
        array $context = []
    ): string;

    public function embedding(
        string $text
    ): array;

    public function available(): bool;
}
