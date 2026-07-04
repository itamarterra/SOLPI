<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

final class ZabbixController
{
    private ZabbixService $service;

    public function __construct()
    {
        $this->service = new ZabbixService();
    }

    /**
     * @return array<string,mixed>
     */
    public function ingest(array $payload): array
    {
        return $this->service->ingest($payload);
    }

    /**
     * @return array<string,int>
     */
    public function summary(): array
    {
        return $this->service->summary();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 20): array
    {
        return $this->service->recent($limit);
    }

    /**
     * @return array<string,mixed>
     */
    public function acknowledge(int $alertId): array
    {
        return $this->service->acknowledge($alertId);
    }
}

