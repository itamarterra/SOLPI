<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use SOLPI\Modules\Intelligence\Repositories\GraphRepository;
use SOLPI\Core\Database\DatabaseManager;

/**
 * Orquestrador do Grafo de Relacionamentos
 */
final class RelationshipManager
{
    private GraphRepository $repository;
    private DatabaseManager $db;

    public function __construct()
    {
        $this->repository = new GraphRepository();
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Mapeia um chamado e seus relacionamentos imediatos no GLPI
     */
    public function indexTicket(int $ticketId): void
    {
        $ticket = $this->db->table('glpi_tickets')
            ->where(['id' => $ticketId])
            ->first();

        if (!$ticket) return;

        $canonicalId = "Ticket:" . $ticketId;

        // 1. Salva o nó do Chamado
        $this->repository->upsertNode($canonicalId, 'Ticket', $ticket['name'], [
            'status' => $ticket['status'],
            'date'   => $ticket['date']
        ]);

        // 2. Localiza Ativos vinculados ao chamado
        $items = $this->db->table('glpi_items_tickets')
            ->where(['tickets_id' => $ticketId])
            ->get();

        foreach ($items as $item) {
            $itemType = $item['itemtype'];
            $itemId   = $item['items_id'];
            $targetId = "{$itemType}:{$itemId}";

            // Cria nó do ativo
            $this->repository->upsertNode($targetId, $itemType);

            // Relaciona Chamado -> Ativo
            $this->repository->addEdge($canonicalId, $targetId, 'AFFECTS', 1.0);
        }

        // 3. Localiza Solicitante
        if ($ticket['users_id_recipient'] > 0) {
            $userId = "User:" . $ticket['users_id_recipient'];
            $this->repository->upsertNode($userId, 'User');
            $this->repository->addEdge($userId, $canonicalId, 'OPENED', 1.0);
        }
    }

    /**
     * Tenta descobrir dependências indiretas (Ativo -> Serviço -> Negócio)
     */
    public function discoverDependencies(string $itemType, int $itemId): void
    {
        $sourceId = "{$itemType}:{$itemId}";

        // Busca no CMDB do GLPI (Dependências de Itens)
        // Nota: GLPI usa a tabela glpi_appliances_items ou relacionamentos de rede
        $relations = $this->db->table('glpi_infocomms')
            ->where(['items_id' => $itemId, 'itemtype' => $itemType])
            ->get();

        foreach ($relations as $rel) {
            // Lógica para expandir a topologia será refinada no Módulo 3
            // Por enquanto, registramos o ponto de entrada
        }
    }
}
