<?php

declare(strict_types=1);

namespace SOLPI\Modules\Topology\Services;

use SOLPI\Modules\Discovery\Adapters\SNMPAdapter;
use SOLPI\Modules\Infrastructure\Services\InfraManager;

/**
 * Mapeador de Topologia de Camada 2 (Link Layer)
 */
final class L2Mapper
{
    private SNMPAdapter $snmp;
    private InfraManager $infra;

    public function __construct()
    {
        $this->snmp = new SNMPAdapter();
        $this->infra = new InfraManager();
    }

    /**
     * Mapeia os vizinhos físicos de um ativo de rede
     */
    public function mapNeighbors(string $ip, string $nodeUuid): void
    {
        $neighbors = $this->snmp->getNeighbors($ip);

        foreach ($neighbors as $neighbor) {
            // 1. Registra o vizinho no Digital Twin (se não existir)
            $remoteUuid = $this->infra->registerAsset(
                'NetworkNode',
                $neighbor['remote_name'],
                null,
                ['source' => 'TopologyEngine', 'protocol' => $neighbor['protocol']]
            );

            // 2. Cria a conexão física (Aresta) entre os dois no Grafo
            $this->infra->connect(
                $nodeUuid,
                $remoteUuid,
                'PHYSICAL_LINK',
                1.0, // Alta confiança por vir de LLDP/CDP
                $neighbor['protocol']
            );
        }
    }
}
