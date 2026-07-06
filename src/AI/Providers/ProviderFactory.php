<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

use SOLPI\Core\Config;
use InvalidArgumentException;

final class ProviderFactory
{
    /**
     * Cria um provider específico
     */
    public function create(string $provider): AIProviderInterface
    {
        return match (strtolower($provider)) {
            'openai' => new OpenAIProvider(),
            'gemini' => new GeminiProvider(),
            'ollama' => new OllamaProvider(),
            'claude' => new ClaudeProvider(),
            'azure'  => new AzureProvider(),
            default  => throw new InvalidArgumentException('Provider inválido.')
        };
    }

    /**
     * Cria o provider configurado como padrão no sistema.
     */
    public function createDefault(): AIProviderInterface
    {
        $cfg = new Config();
        $cfg->load();
        $aiCfg = $cfg->get('ai', []);

        if (!($aiCfg['enabled'] ?? false)) {
            // Fallback para um provider "silencioso" ou erro se a IA for obrigatória
            // Por enquanto, tentaremos retornar o configurado mesmo assim para o teste
        }

        $providerName = $aiCfg['provider'] ?? 'openai';
        return $this->create($providerName);
    }
}
