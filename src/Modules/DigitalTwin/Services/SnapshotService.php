<?php

declare(strict_types=1);

namespace SOLPI\Modules\DigitalTwin\Services;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Modules\DigitalTwin\Entities\Snapshot;
use SOLPI\Modules\DigitalTwin\Repositories\SnapshotRepository;

/**
 * Serviço para capturar e gerenciar Snapshots da Infraestrutura.
 */
final class SnapshotService
{
    private DatabaseManager $db;
    private SnapshotRepository $repository;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->repository = new SnapshotRepository();
    }

    /**
     * Captura o estado atual de todos os nós e arestas.
     */
    public function capture(string $name): int
    {
        $nodes = iterator_to_array($this->db->table('glpi_plugin_solpi_inframap_nodes')->get());
        $edges = iterator_to_array($this->db->table('glpi_plugin_solpi_inframap_edges')->get());

        $data = [
            'nodes' => $nodes,
            'edges' => $edges
        ];

        $snapshot = new Snapshot($name, $data);
        return $this->repository->create($snapshot);
    }

    /**
     * Retorna o snapshot mais recente.
     */
    public function getLatest(): ?Snapshot
    {
        return $this->repository->findLatest();
    }
}
