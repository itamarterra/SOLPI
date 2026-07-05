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
    private \SOLPI\Modules\AI\Embeddings $embeddings;
    private SimilarityService $similarity;
    private DependencyResolver $dependencyResolver;
    private AssetLookupService $assetLookup;

    public function __construct()
    {
        $this->repository = new GraphRepository();
        $this->db = DatabaseManager::getInstance();
        $this->embeddings = new \SOLPI\Modules\AI\Embeddings();
        $this->similarity = new SimilarityService();
        $this->dependencyResolver = new DependencyResolver();
        $this->assetLookup = new AssetLookupService();
    }

    /**
     * Mapeia um alerta do Zabbix no Grafo
     */
    public function indexZabbixAlert(array $alertData): void
    {
        $alertId = "Alert:" . ($alertData['id'] ?? uniqid());

        // 1. Cria nó do Alerta
        $this->repository->upsertNode($alertId, 'ZabbixAlert', $alertData['trigger_name'], [
            'severity' => $alertData['severity'],
            'host'     => $alertData['host']
        ]);

        // 2. Tenta localizar o Ativo no GLPI
        $asset = $this->assetLookup->findByHost($alertData['host']);
        if ($asset) {
            $assetId = "{$asset['type']}:{$asset['id']}";

            // Cria nó do ativo e relaciona
            $this->repository->upsertNode($assetId, $asset['type']);
            $this->repository->addEdge($alertId, $assetId, 'MONITORS', 1.0);

            // 3. Expande dependências do Ativo
            $this->discoverDependencies($asset['type'], $asset['id']);

            // 4. Procura chamados abertos para este ativo ou dependentes
            $this->linkRelatedTickets($assetId);
        }
    }

    /**
     * Tenta vincular chamados existentes ao ativo afetado
     */
    private function linkRelatedTickets(string $assetId): void
    {
        // Busca chamados abertos que afetam este ativo
        [$type, $id] = explode(':', $assetId);

        $tickets = $this->db->table('glpi_items_tickets')
            ->where(['items_id' => $id, 'itemtype' => $type])
            ->get();

        foreach ($tickets as $t) {
            $ticketId = "Ticket:" . $t['tickets_id'];
            $this->repository->addEdge($assetId, $ticketId, 'HAS_TICKET', 1.0);
        }
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

        // 2. Processamento Semântico (Embeddings)
        $vector = $this->embeddings->generate($ticket['name'] . " " . strip_tags($ticket['content']));
        $this->embeddings->store('Ticket', $ticketId, $vector);

        // 3. Busca por chamados semelhantes no Grafo
        $similar = $this->similarity->findSimilar($vector, 'Ticket', 0.85);
        foreach ($similar as $match) {
            if ($match['id'] === $ticketId) continue;

            $this->repository->addEdge(
                $canonicalId,
                "Ticket:" . $match['id'],
                'SIMILAR',
                $match['score']
            );
        }

        // 4. Localiza Ativos vinculados ao chamado e suas dependências
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

            // 5. Expande a árvore de dependências do Ativo
            $this->discoverDependencies($itemType, $itemId);
        }

        // 6. Localiza Solicitante
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
        $tree = $this->dependencyResolver->resolve($itemType, $itemId);

        // Processa Upstream (Quem o ativo precisa para funcionar)
        foreach ($tree['upstream'] as $dep) {
            $targetId = "{$dep['type']}:{$dep['id']}";
            $this->repository->upsertNode($targetId, $dep['type']);
            $this->repository->addEdge($sourceId, $targetId, 'DEPENDS_ON', 1.0);
        }

        // Processa Downstream (Quem depende do ativo atual)
        foreach ($tree['downstream'] as $dep) {
            $targetId = "{$dep['type']}:{$dep['id']}";
            $this->repository->upsertNode($targetId, $dep['type']);
            $this->repository->addEdge($sourceId, $targetId, 'SUPPORTS', 1.0);
        }
    }
}
