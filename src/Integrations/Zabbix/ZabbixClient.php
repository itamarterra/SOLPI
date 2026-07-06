<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class ZabbixClient
{
    private ZabbixHttpClient $http;

    public function __construct(
        ZabbixHttpClient $http,
        private readonly ZabbixModel $config
    ) {
        $this->http = $http;
    }

    public function request(string $method, array $params = []): array
    {
        $response = $this->http->post(
            $this->config->url . '/api_jsonrpc.php',
            [
                'Content-Type' => 'application/json'
            ],
            [
                'jsonrpc' => '2.0',
                'method'  => $method,
                'params'  => $params,
                'id'      => 1
            ],
            $this->config->token // Passa o token para ser enviado via Header
        );

        return $response['body'] ?? [];
    }
}
