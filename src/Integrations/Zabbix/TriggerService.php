<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class TriggerService
{
    public function __construct(
        private readonly ZabbixClient $client
    ) {
    }

    public function active(): array
    {
        return $this->client->request(

            'trigger.get',

            [
                'output' => 'extend',
                'filter' => [
                    'value' => 1
                ]
            ]

        );
    }
}
