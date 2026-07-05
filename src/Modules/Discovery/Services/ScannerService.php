<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Services;

use SOLPI\Modules\Discovery\Adapters\SNMPAdapter;
use SOLPI\Modules\Discovery\Adapters\ICMPAdapter;
use SOLPI\Modules\Infrastructure\Services\InfraManager;

/**
 * Orquestrador de varreduras de rede para o Discovery Engine.
 */
final class ScannerService
{
    private array $adapters = [];
    private InfraManager $infraManager;

    public function __construct()
    {
        $this->infraManager = new InfraManager();

        // Prioridade: Protocolos mais ricos primeiro
        $this->adapters[] = new SNMPAdapter();
        $this->adapters[] = new ICMPAdapter();
    }

    /**
     * Varre uma faixa de IPs (ex: 192.168.1.1 até 192.168.1.254)
     */
    public function scanRange(string $startIp, string $endIp): array
    {
        $start = ip2long($startIp);
        $end = ip2long($endIp);
        $results = [];

        for ($i = $start; $i <= $end; $i++) {
            $ip = long2ip($i);
            $deviceData = $this->discoverDevice($ip);

            if ($deviceData) {
                $results[$ip] = $deviceData;

                // Registra no Digital Twin via InfraManager
                $this->infraManager->registerAsset(
                    $deviceData['type'] ?? 'Asset',
                    $deviceData['name'] ?? $ip,
                    null,
                    array_merge($deviceData, ['ip' => $ip, 'source' => 'DiscoveryEngine'])
                );
            }
        }

        return $results;
    }

    /**
     * Tenta identificar um dispositivo usando todos os protocolos disponíveis
     */
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
