<?php

declare(strict_types=1);

$envBool = static function (string $name, bool $default = false): bool {
    $raw = getenv($name);
    if ($raw === false || $raw === '') {
        return $default;
    }

    $value = strtolower(trim((string)$raw));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
};

return [

    'evolution' => [
        'enabled'        => $envBool('SOLPI_EVOLUTION_ENABLED', true),
        'base_url'       => getenv('SOLPI_EVOLUTION_BASE_URL') ?: 'http://evolution-api:8080',
        'instance'       => getenv('SOLPI_EVOLUTION_INSTANCE') ?: 'solpi',
        'auth_key'       => getenv('SOLPI_WEBHOOK_SECRET') ?: getenv('SOLPI_EVOLUTION_TOKEN') ?: 'solpi123',
        'api_key_header' => 'apikey',
    ],

    'zabbix' => [
        'enabled'  => true,
        'base_url' => getenv('SOLPI_ZABBIX_URL') ?: 'http://zabbix-web:8080',
        'token'    => '013b6bdc5dfa3f943e10a2ee8a66c2e151c1ab1afb7769c824d45f1691f6d57f',
    ],

    'ai' => [
        'enabled'  => $envBool('SOLPI_AI_ENABLED', false),
        'provider' => getenv('SOLPI_AI_PROVIDER') ?: 'openai',
        'model'    => getenv('SOLPI_AI_MODEL') ?: 'gpt-4o',
        'api_key'  => getenv('SOLPI_AI_API_KEY') ?: '',
    ],

];
