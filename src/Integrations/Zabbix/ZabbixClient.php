<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

use SOLPI\Contracts\HttpClientInterface;

final class ZabbixClient
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly ZabbixModel $config
    ) {
    }

    public function request(
        string $method,
        array $params = []
    ): array {

        return $this->http->post(

            $this->config->url . '/api_jsonrpc.php',

            [
                'Content-Type' => 'application/json-rpc'
            ],

            [
                'jsonrpc' => '2.0',
                'method'   => $method,
                'params'   => $params,
                'auth'     => $this->config->token,
                'id'       => 1
            ]

        );

    }
}
