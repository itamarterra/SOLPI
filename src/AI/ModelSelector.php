<?php

declare(strict_types=1);

namespace SOLPI\AI;

use SOLPI\Core\Config;

/**
 * Seleciona o modelo de IA baseado na configuração e complexidade da tarefa
 */
final class ModelSelector
{
    private array $config;

    public function __construct()
    {
        $cfg = new Config();
        $cfg->load();
        $this->config = $cfg->get('ai', []);
    }

    public function getSelectedProvider(): string
    {
        return $this->config['provider'] ?? 'openai';
    }

    public function getSelectedModel(): string
    {
        return $this->config['model'] ?? 'gpt-4o';
    }

    public function getApiKey(): string
    {
        return $this->config['api_key'] ?? '';
    }

    /**
     * Define qual modelo usar baseado no tipo de requisição
     */
    public function getModelForTask(string $taskType): string
    {
        $provider = $this->getSelectedProvider();

        if ($provider === 'openai') {
            return $taskType === 'complex' ? 'gpt-4o' : 'gpt-4o-mini';
        }

        if ($provider === 'gemini') {
            return 'gemini-1.5-pro';
        }

        return $this->getSelectedModel();
    }
}
