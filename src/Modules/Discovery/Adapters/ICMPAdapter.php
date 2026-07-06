<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Adapters;

use SOLPI\Modules\Discovery\Contracts\DiscoveryAdapterInterface;

/**
 * Adaptador de Descoberta via ICMP (Ping)
 */
final class ICMPAdapter implements DiscoveryAdapterInterface
{
    public function getProtocol(): string { return 'ICMP'; }

    public function isSupported(): bool { return true; }

    public function discover(string $ip): ?array
    {
        $status = $this->ping($ip);

        if (!$status) {
            return null;
        }

        return [
            'name' => "Host " . $ip,
            'description' => "Dispositivo respondendo a ICMP",
            'type' => 'Unknown'
        ];
    }

    private function ping(string $ip): bool
    {
        // Reduzido timeout para 200ms para varredura mais rápida
        $cmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            ? "ping -n 1 -w 200 " . escapeshellarg($ip)
            : "ping -c 1 -W 0.2 " . escapeshellarg($ip) . " > /dev/null 2>&1";

        exec($cmd, $output, $result);

        return $result === 0;
    }
}
