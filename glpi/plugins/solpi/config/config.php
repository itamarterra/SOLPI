<?php

declare(strict_types=1);

return [

    'evolution' => [
        'enabled'        => true,
        'base_url'       => 'http://evolution-api:8080',
        'instance'       => 'solpi',
        'auth_key'       => 'solpi123',
        'api_key_header' => 'apikey',
    ],

    'zabbix' => [
        'enabled'  => false,
        'base_url' => '',
        'token'    => '',
    ],

    'ai' => [
        'enabled'  => false,
        'provider' => 'openai',
        'model'    => 'gpt-4o',
        'api_key'  => '',
    ],

];

