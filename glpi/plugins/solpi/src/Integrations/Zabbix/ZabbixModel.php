<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

final class ZabbixModel
{
    public function __construct(
        public readonly string $url,
        public readonly string $token
    ) {
    }
}
