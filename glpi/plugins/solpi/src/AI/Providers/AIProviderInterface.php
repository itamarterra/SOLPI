<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

interface AIProviderInterface
{
    public function name(): string;

    /**
     * Send a chat prompt to the provider.
     *
     * @param string $prompt
     * @param array<string,mixed> $context
     * @return string
     */
    public function chat(string $prompt, array $context = []): string;

    /**
     * @param string $text
     * @return array<int,float>
     */
    public function embedding(string $text): array;

    public function available(): bool;
}
