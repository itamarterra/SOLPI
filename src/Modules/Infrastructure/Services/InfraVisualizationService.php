<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Core\Database\DatabaseManager;

/**
 * Serviço para gerar a representação visual do Digital Twin.
 * Formata os dados para bibliotecas de visualização como Vis.js.
 */
final class InfraVisualizationService
{
    private DatabaseManager $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Retorna o dataset completo do Digital Twin (Global)
     */
    public function getGlobalMap(): array
    {
        $nodes = [];
        $edges = [];

        // 1. Busca todos os nós do Digital Twin
        $nodeRows = $this->db->table('glpi_plugin_solpi_inframap_nodes')->get();
        foreach ($nodeRows as $row) {
            $nodes[] = [
                'id'    => $row['uuid'],
                'label' => $row['label'],
                'group' => $row['class'],
                'title' => $this->formatTitle($row)
            ];
        }

        // 2. Busca todas as arestas (relacionamentos)
        $edgeRows = $this->db->table('glpi_plugin_solpi_inframap_edges')->get();
        foreach ($edgeRows as $row) {
            $edges[] = [
                'from'   => $row['source_uuid'],
                'to'     => $row['target_uuid'],
                'label'  => $row['relation_type'],
                'arrows' => 'to',
                'color'  => $this->getRelationColor($row['relation_type'])
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }

    private function formatTitle(array $row): string
    {
        $meta = json_decode((string)$row['metadata'], true) ?: [];
        $title = "<b>" . htmlspecialchars($row['label']) . "</b><br>";
        $title .= "Classe: " . $row['class'] . "<br>";
        foreach ($meta as $k => $v) {
            if (is_scalar($v)) $title .= ucfirst($k) . ": $v<br>";
        }
        return $title;
    }

    private function getRelationColor(string $type): string
    {
        return match($type) {
            'PHYSICAL_LINK' => '#3b82f6', // Azul
            'DEPENDS_ON'    => '#ef4444', // Vermelho
            'SUPPORTS'      => '#10b981', // Verde
            'RUNS_ON'       => '#8b5cf6', // Roxo
            default         => '#94a3b8'  // Cinza
        };
    }
}
