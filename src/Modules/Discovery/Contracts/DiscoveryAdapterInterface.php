<?php

declare(strict_types=1);

namespace SOLPI\Modules\Discovery\Contracts;

/**
 * Interface para adaptadores de descoberta (SNMP, WMI, SSH, etc.)
 */
interface DiscoveryAdapterInterface
{
    /**
     * Retorna o nome do protocolo (ex: SNMP, WMI)
     */
    public function getProtocol(): string;

    /**
     * Tenta descobrir detalhes de um dispositivo no IP fornecido.
     *
     * @return array<string, mixed>|null Dados descobertos ou null se falhar.
     */
    public function discover(string $ip): ?array;

    /**
     * Verifica se o adaptador está disponível no servidor (extensões instaladas, etc.)
     */
    public function isSupported(): bool;
}
