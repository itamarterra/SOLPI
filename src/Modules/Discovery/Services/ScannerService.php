<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Services;

use SOLPI\Modules\Discovery\Adapters\SNMPAdapter;
use SOLPI\Modules\Discovery\Adapters\ICMPAdapter;
use SOLPI\Modules\Discovery\Adapters\ZabbixAdapter;
use SOLPI\Modules\Infrastructure\Services\InfraManager;

/**
 * Orquestrador de varreduras com Inteligência de Classificação
 */
final class ScannerService
{
    private array $adapters = [];
    private ZabbixAdapter $zabbixAdapter;

    public function __construct()
    {
        $this->zabbixAdapter = new ZabbixAdapter();
        $this->adapters[] = $this->zabbixAdapter;
        $this->adapters[] = new SNMPAdapter();
        $this->adapters[] = new ICMPAdapter();
    }

    public function syncFromZabbix(): array
    {
        $hosts = $this->zabbixAdapter->fetchAllHosts();
        $results = [];
        $hostIds = array_column($hosts, 'hostid');
        $incidents = $this->zabbixAdapter->fetchActiveIncidents($hostIds);

        foreach ($hosts as $host) {
            $ip = $host['interfaces'][0]['ip'] ?? '0.0.0.0';
            $name = $host['name'] ?: $host['host'];

            $results[$ip] = [
                'name'        => $name,
                'description' => $host['description'] ?: 'Monitorado pelo Zabbix',
                'type'        => $this->inferTypeByName($name), // Inteligência aqui!
                'protocol'    => 'ZabbixAPI',
                'external_id' => 'zabbix_' . $host['hostid'],
                'incidents'   => []
            ];
        }
        return $results;
    }

    private function inferTypeByName(string $name): string
    {
        $n = strtoupper($name);
        if (str_contains($n, 'ROTEADOR') || str_contains($n, 'ROUTER')) return 'Router';
        if (str_contains($n, 'SWITCH') || str_contains($n, 'SW-')) return 'Switch';
        if (str_contains($n, 'CELULAR') || str_contains($n, 'PHONE')) return 'Mobile';
        if (str_contains($n, 'PC') || str_contains($n, 'NOTEBOOK')) return 'Computer';
        if (str_contains($n, 'PRINTER') || str_contains($n, 'IMPRESSORA')) return 'Printer';
        return 'Server';
    }

    public function getLastError(): string { return $this->zabbixAdapter->getLastError(); }

    public function scanRange(string $startIp, string $endIp): array
    {
        $start = ip2long($startIp); $end = ip2long($endIp);
        $results = [];
        for ($i = $start; $i <= $end; $i++) {
            $ip = long2ip($i);
            $data = $this->discoverDevice($ip);
            if ($data) $results[$ip] = $data;
        }
        return $results;
    }

    private function discoverDevice(string $ip): ?array
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->isSupported()) {
                $data = $adapter->discover($ip);
                if ($data) {
                    $data['protocol'] = $adapter->getProtocol();
                    return $data;
                }
            }
        }
        return null;
    }
}
