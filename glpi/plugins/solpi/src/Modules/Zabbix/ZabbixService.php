<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

final class ZabbixService
{
    private ZabbixRepository $repository;

    public function __construct()
    {
        $this->repository = new ZabbixRepository();
    }

    /**
     * @return array<string,mixed>
     */
    public function ingest(array $payload): array
    {
        $eventId = $payload['eventid']
            ?? $payload['event_id']
            ?? ($payload['event']['id'] ?? null);

        $host = $payload['host']
            ?? ($payload['event']['host'] ?? null)
            ?? ($payload['data']['host'] ?? null)
            ?? 'unknown';

        $triggerName = $payload['trigger_name']
            ?? $payload['trigger']
            ?? ($payload['event']['name'] ?? null)
            ?? 'Zabbix alert';

        $severity = $payload['severity']
            ?? ($payload['event']['severity'] ?? null)
            ?? 'warning';

        $status = $payload['status']
            ?? ($payload['event']['status'] ?? null)
            ?? 'OPEN';

        $alertId = $this->repository->create([
            'eventid' => $eventId,
            'host' => (string)$host,
            'trigger_name' => (string)$triggerName,
            'severity' => (string)$severity,
            'status' => (string)$status,
            'raw_data' => $payload,
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

