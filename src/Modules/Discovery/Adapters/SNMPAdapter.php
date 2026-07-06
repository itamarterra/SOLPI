<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Adapters;

use SOLPI\Modules\Discovery\Contracts\DiscoveryAdapterInterface;
use Exception;

/**
 * Adaptador de Descoberta via SNMP (Agnóstico de Fabricante)
 * Versão 2.1 - Estabilizada e com suporte a Topologia L2
 */
final class SNMPAdapter implements DiscoveryAdapterInterface
{
    private string $community;
    private int $timeout;
    private int $retries;

    public function __construct(string $community = 'public', int $timeout = 100000, int $retries = 0)
    {
        $this->community = $community;
        $this->timeout = $timeout; // Reduzido para 100ms para descoberta rápida
        $this->retries = $retries; // 0 retentativas para velocidade
    }

    public function getProtocol(): string { return 'SNMP'; }

    public function isSupported(): bool
    {
        return function_exists('snmpget') && function_exists('snmprealwalk');
    }

    public function discover(string $ip): ?array
    {
        if (!$this->isSupported()) {
            return null;
        }

        // Suprime avisos temporários para pings SNMP silenciosos
        $oldLevel = error_reporting();
        error_reporting($oldLevel & ~E_WARNING);

        try {
            $sysDescr = @\snmpget($ip, $this->community, ".1.3.6.1.2.1.1.1.0", $this->timeout, $this->retries);
            if ($sysDescr === false) return null;

            $sysName  = @\snmpget($ip, $this->community, ".1.3.6.1.2.1.1.5.0", $this->timeout, $this->retries);
            $sysObjectID = @\snmpget($ip, $this->community, ".1.3.6.1.2.1.1.2.0", $this->timeout, $this->retries);

            return [
                'name'        => $this->cleanValue($sysName),
                'description' => $this->cleanValue($sysDescr),
                'object_id'   => $this->cleanValue($sysObjectID),
                'type'        => $this->inferType($this->cleanValue($sysDescr))
            ];
        } catch (Exception) {
            return null;
        } finally {
            error_reporting($oldLevel);
        }
    }

    /**
     * Busca vizinhos de rede via LLDP ou CDP
     */
    public function getNeighbors(string $ip): array
    {
        if (!$this->isSupported()) {
            return [];
        }

        $neighbors = [];

        // 1. Tenta LLDP (Padrão 802.1AB)
        $lldpNames = @\snmprealwalk($ip, $this->community, ".1.0.8802.1.1.2.1.4.1.1.9", $this->timeout, $this->retries);
        if ($lldpNames) {
            foreach ($lldpNames as $oid => $val) {
                $neighbors[] = [
                    'remote_name' => $this->cleanValue($val),
                    'protocol'    => 'LLDP'
                ];
            }
        }

        // 2. Tenta CDP (Cisco)
        if (empty($neighbors)) {
            $cdpNames = @\snmprealwalk($ip, $this->community, ".1.3.6.1.4.1.9.9.23.1.2.1.1.6", $this->timeout, $this->retries);
            if ($cdpNames) {
                foreach ($cdpNames as $oid => $val) {
                    $neighbors[] = [
                        'remote_name' => $this->cleanValue($val),
                        'protocol'    => 'CDP'
                    ];
                }
            }
        }

        return $neighbors;
    }

    /**
     * Busca a lista de VLANs configuradas no equipamento
     */
    public function getVlans(string $ip): array
    {
        if (!$this->isSupported()) {
            return [];
        }

        $vlans = [];
        $names = @\snmprealwalk($ip, $this->community, ".1.3.6.1.2.1.17.7.1.4.3.1.1", $this->timeout, $this->retries);

        if ($names) {
            foreach ($names as $oid => $val) {
                $id = (int)substr($oid, strrpos($oid, '.') + 1);
                $vlans[$id] = $this->cleanValue($val);
            }
        }
        return $vlans;
    }

    private function cleanValue($val): string
    {
        if (!$val) return '';
        return preg_replace('/^(STRING|OID|INTEGER|Gauge32|Counter32|Hex-STRING):\s+/i', '', trim((string)$val, '" '));
    }

    private function inferType(string $descr): string
    {
        $descr = strtolower($descr);
        if (str_contains($descr, 'switch')) return 'Switch';
        if (str_contains($descr, 'router') || str_contains($descr, 'gateway')) return 'Router';
        if (str_contains($descr, 'printer') || str_contains($descr, 'laserjet')) return 'Printer';
        if (str_contains($descr, 'windows') || str_contains($descr, 'linux')) return 'Server/PC';
        return 'NetworkNode';
    }
}
