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
        'auth_key'       => getenv('SOLPI_WEBHOOK_SECRET') ?: getenv('SOLPI_EVOLUTION_TOKEN') ?: '',
        'api_key_header' => 'apikey',
    ],

    'zabbix' => [
        'enabled'  => $envBool('SOLPI_ZABBIX_ENABLED', false),
        'base_url' => getenv('SOLPI_ZABBIX_URL') ?: '',
        'token'    => getenv('SOLPI_ZABBIX_TOKEN') ?: '',
    ],

    'ai' => [
        'enabled'  => $envBool('SOLPI_AI_ENABLED', false),
        'provider' => getenv('SOLPI_AI_PROVIDER') ?: 'openai',
        'model'    => getenv('SOLPI_AI_MODEL') ?: 'gpt-4o',
        'api_key'  => getenv('SOLPI_AI_API_KEY') ?: '',
    ],

];

