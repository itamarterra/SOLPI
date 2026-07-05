<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Core\Database\DatabaseManager;

/**
 * Motor de descoberta de dependências técnicas e de negócio do GLPI
 */
final class DependencyResolver
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Resolve a árvore de dependências subindo (Upstream - quem sustenta)
     * e descendo (Downstream - quem é afetado)
     *
     * @return array{upstream: array, downstream: array}
     */
    public function resolve(string $itemType, int $itemId): array
    {
        return [
            'upstream'   => $this->getUpstream($itemType, $itemId),
            'downstream' => $this->getDownstream($itemType, $itemId)
        ];
    }

    /**
     * Busca itens dos quais o item atual depende (Ex: Servidor depende de Switch)
     */
    private function getUpstream(string $itemType, int $itemId): array
    {
        $dependencies = [];

        // No GLPI, relacionamentos entre itens ficam na glpi_items_items
        $rows = $this->db->table('glpi_items_items')
            ->where([
                'items_id_2'   => $itemId,
                'itemtype_2'   => $itemType
            ])->get();

        foreach ($rows as $row) {
            $dependencies[] = [
                'type' => $row['itemtype_1'],
                'id'   => (int)$row['items_id_1'],
                'relation' => 'DEPENDS_ON'
            ];
        }

        return $dependencies;
    }

    /**
     * Busca itens que dependem do item atual (Ex: ERP depende do Banco de Dados)
     */
    private function getDownstream(string $itemType, int $itemId): array
    {
        $dependents = [];

        $rows = $this->db->table('glpi_items_items')
            ->where([
                'items_id_1'   => $itemId,
                'itemtype_1'   => $itemType
            ])->get();

        foreach ($rows as $row) {
            $dependents[] = [
                'type' => $row['itemtype_2'],
                'id'   => (int)$row['items_id_2'],
                'relation' => 'SUPPORTS'
            ];
        }

        return $dependents;
    }

    /**
     * Localiza o Serviço de Negócio vinculado ao ativo
     */
    public function getBusinessServices(string $itemType, int $itemId): array
    {
        // Implementação simplificada buscando categorias vinculadas ou itens de serviço
        // No GLPI 10+, serviços podem estar em glpi_businessreviews ou relations customizadas
        return [];
    }
}
