<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Adapters;

use SOLPI\Modules\Discovery\Contracts\DiscoveryAdapterInterface;
use Exception;

/**
 * Adaptador de Descoberta via SNMP (Agnóstico de Fabricante)
 */
final class SNMPAdapter implements DiscoveryAdapterInterface
{
    private string $community;
    private int $timeout;
    private int $retries;

    public function __construct(string $community = 'public', int $timeout = 1000000, int $retries = 1)
    {
        $this->community = $community;
        $this->timeout = $timeout;
        $this->retries = $retries;
    }

    public function getProtocol(): string { return 'SNMP'; }

    public function isSupported(): bool
    {
        return function_exists('snmpget');
    }

    public function discover(string $ip): ?array
    {
        if (!$this->isSupported()) {
            return null;
        }

        // Suprime erros para evitar poluição no log se o host não responder SNMP
        error_reporting(error_reporting() & ~E_WARNING);

        try {
            // OIDs Padrão (RFC 1213)
            $sysDescr = @snmpget($ip, $this->community, ".1.3.6.1.2.1.1.1.0", $this->timeout, $this->retries);
            $sysName  = @snmpget($ip, $this->community, ".1.3.6.1.2.1.1.5.0", $this->timeout, $this->retries);
            $sysObjectID = @snmpget($ip, $this->community, ".1.3.6.1.2.1.1.2.0", $this->timeout, $this->retries);

            if ($sysDescr === false) {
                return null;
            }

            return [
                'name'        => $this->cleanValue($sysName),
                'description' => $this->cleanValue($sysDescr),
                'object_id'   => $this->cleanValue($sysObjectID),
                'type'        => $this->inferType($this->cleanValue($sysDescr))
            ];
        } catch (Exception) {
            return null;
        } finally {
            error_reporting(error_reporting() | E_WARNING);
        }
    }

    private function cleanValue($val): string
    {
        if (!$val) return '';
        // Remove prefixos como "STRING: " que alguns binários SNMP retornam
        return preg_replace('/^(STRING|OID|INTEGER|Gauge32|Counter32):\s+/i', '', trim((string)$val, '" '));
    }

    private function inferType(string $descr): string
    {
        $descr = strtolower($descr);
        if (str_contains($descr, 'switch')) return 'Switch';
        if (str_contains($descr, 'router') || str_contains($descr, 'gateway')) return 'Router';
        if (str_contains($descr, 'printer') || str_contains($descr, 'laserjet')) return 'Printer';
        if (str_contains($descr, 'windows')) return 'Server/PC';
        if (str_contains($descr, 'linux')) return 'Server/PC';

        return 'NetworkNode';
    }
}
