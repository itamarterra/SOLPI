<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

use InvalidArgumentException;

final class ProviderFactory
{
    public function create(
        string $provider
    ): AIProviderInterface{

        return match(strtolower($provider)){

            'openai'=>new OpenAIProvider(),

            'gemini'=>new GeminiProvider(),

            'ollama'=>new OllamaProvider(),

            'claude'=>new ClaudeProvider(),

            'azure'=>new AzureProvider(),

            default=>throw new InvalidArgumentException(

                'Provider inválido.'

            )

        };

    }
}
