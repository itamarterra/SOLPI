<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

final class ZabbixService
{
    private ZabbixRepository $repository;
    private \SOLPI\Modules\Intelligence\Services\RelationshipManager $intelligence;

    public function __construct()
    {
        $this->repository = new ZabbixRepository();
        $this->intelligence = new \SOLPI\Modules\Intelligence\Services\RelationshipManager();
    }

    /**
     * @return array<string,mixed>
     */
    public function ingest(array $payload): array
    {
        // ... (resto do código igual)
        $alertId = $this->repository->create([
            'eventid' => $eventId,
            'host' => (string)$host,
            'trigger_name' => (string)$triggerName,
            'severity' => (string)$severity,
            'status' => (string)$status,
            'raw_data' => $payload,
        ]);

        // INTELLIGÊNCIA: Mapeia o alerta no Grafo de Incidentes
        $this->intelligence->indexZabbixAlert([
            'id'           => $alertId,
            'host'         => $host,
            'trigger_name' => $triggerName,
            'severity'     => $severity
        ]);

        return [
            'status' => 'stored',
            'alert_id' => $alertId,
        ];
    }

    /**
     * @return array<string,int>
     */
    public function summary(): array
    {
        return $this->repository->summary();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 20): array
    {
        return $this->repository->recent($limit);
    }

    /**
     * @return array<string,mixed>
     */
    public function acknowledge(int $alertId): array
    {
        $this->repository->acknowledge($alertId);

        return [
            'status' => 'acknowledged',
            'alert_id' => $alertId,
        ];
    }
}

