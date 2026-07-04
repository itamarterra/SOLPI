<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class EventService
{
    public function __construct(
        private readonly ZabbixClient $client
    ) {
    }

    public function latest(): array
    {
        return $this->client->request(

            'event.get',

            [
                'output'    => 'extend',
                'sortfield' => 'clock',
                'sortorder' => 'DESC',
                'limit'     => 100
            ]

        );
    }
}
