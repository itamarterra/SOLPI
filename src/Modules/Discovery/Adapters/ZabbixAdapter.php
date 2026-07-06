<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Adapters;

use SOLPI\Modules\Discovery\Contracts\DiscoveryAdapterInterface;
use SOLPI\Core\Config;
use SOLPI\Integrations\Zabbix\ZabbixClient;
use SOLPI\Integrations\Zabbix\ZabbixModel;
use SOLPI\Integrations\Zabbix\ZabbixHttpClient;
use Exception;
use Throwable;

/**
 * Adaptador para descobrir ativos que já estão cadastrados no Zabbix
 */
final class ZabbixAdapter implements DiscoveryAdapterInterface
{
    private ?ZabbixClient $client = null;
    private string $lastError = '';

    public function __construct()
    {
        $cfg = new Config();
        $cfg->load();
        $zabbixCfg = $cfg->get('config.zabbix', []);

        if (!empty($zabbixCfg['base_url']) && !empty($zabbixCfg['token'])) {
            $model = new ZabbixModel(
                $zabbixCfg['base_url'],
                $zabbixCfg['token']
            );
            $this->client = new ZabbixClient(new ZabbixHttpClient(), $model);
        }
    }

    public function getProtocol(): string { return 'ZabbixAPI'; }

    public function isSupported(): bool { return $this->client !== null; }

    public function getLastError(): string { return $this->lastError; }

    public function discover(string $ip): ?array
    {
        if (!$this->isSupported()) return null;

        try {
            $response = $this->client->request('host.get', [
                'filter' => ['ip' => $ip],
                'output' => ['hostid', 'host', 'name', 'description'],
                'selectInterfaces' => ['ip']
            ]);

            if (isset($response['error'])) {
                $this->lastError = $response['error']['data'] ?? $response['error']['message'];
                return null;
            }

            $hosts = $response['result'] ?? [];
            if (empty($hosts)) return null;

            $host = $hosts[0];
            return [
                'name'        => $host['name'] ?: $host['host'],
                'description' => $host['description'] ?: 'Importado via Zabbix',
                'external_id' => 'zabbix_' . $host['hostid'],
                'type'        => 'Server'
            ];
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    public function fetchAllHosts(): array
    {
        if (!$this->isSupported()) {
            $this->lastError = 'Zabbix não está configurado.';
            return [];
        }

        try {
            $response = $this->client->request('host.get', [
                'output' => ['hostid', 'host', 'name', 'description'],
                'selectInterfaces' => ['ip'],
                'monitored_hosts' => true
            ]);

            if (isset($response['error'])) {
                $this->lastError = "Zabbix: " . ($response['error']['data'] ?? $response['error']['message']);
                return [];
            }

            return $response['result'] ?? [];
        } catch (Throwable $e) {
            $this->lastError = "Conexão: " . $e->getMessage();
            return [];
        }
    }

    public function fetchActiveIncidents(array $hostIds): array
    {
        if (!$this->isSupported() || empty($hostIds)) return [];

        try {
            $response = $this->client->request('trigger.get', [
                'output' => ['triggerid', 'description', 'priority'],
                'hostids' => $hostIds,
                'filter' => ['value' => 1],
                'monitored' => true,
                'only_true' => true
            ]);

            return $response['result'] ?? [];
        } catch (Throwable) {
            return [];
        }
    }
}
