<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Core\Database\DatabaseManager;

/**
 * Localiza ativos reais do GLPI baseados em informações externas (Hostname/IP)
 */
final class AssetLookupService
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Tenta encontrar um ativo por nome ou IP
     *
     * @return array{type:string, id:int}|null
     */
    public function findByHost(string $host): ?array
    {
        // 1. Procura em Computadores
        $computer = $this->db->table('glpi_computers')
            ->where(['name' => $host])
            ->first();
        if ($computer) return ['type' => 'Computer', 'id' => (int)$computer['id']];

        // 2. Procura em Equipamentos de Rede
        $network = $this->db->table('glpi_networkequipments')
            ->where(['name' => $host])
            ->first();
        if ($network) return ['type' => 'NetworkEquipment', 'id' => (int)$network['id']];

        // 3. Procura por IP na tabela de portas de rede
        $ip = $this->db->table('glpi_ipaddresses')
            ->where(['mainitems_id' => $host]) // Simplificado para o exemplo
            ->first();

        return null;
    }
}
