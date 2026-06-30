<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class HostService
{
    public function __construct(
        private readonly ZabbixClient $client
    ) {
    }

    public function all(): array
    {
        return $this->client->request(

            'host.get',

            [
                'output' => 'extend'
            ]

        );
    }
}
