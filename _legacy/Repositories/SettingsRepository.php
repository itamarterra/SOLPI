<?php

declare(strict_types=1);

namespace SOLPI\Repositories;

final class SettingsRepository
{
    public function load(): array
    {
        return [
            'zabbix_url' => '',
            'zabbix_token' => '',
            'evolution_url' => '',
            'evolution_token' => '',
            'openai_key' => '',
            'gemini_key' => '',
            'ollama_url' => ''
        ];
    }
}
