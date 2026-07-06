<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Core\Database\DatabaseManager;

/**
 * Visualização v4.5 - Nomes e IPs Combinados + Ícones Precisos
 */
final class InfraVisualizationService
{
    private DatabaseManager $db;

    public function __construct() { $this->db = DatabaseManager::getInstance(); }

    public function getGlobalMap(): array
    {
        $nodes = []; $edges = [];
        $nodeRows = $this->db->table('glpi_plugin_solpi_inframap_nodes')->get();

        foreach ($nodeRows as $row) {
            $meta = json_decode((string)$row['metadata'], true) ?: [];
            $status = $meta['status'] ?? 'ONLINE';
            $ip = $meta['ip'] ?? '';

            $nodes[] = [
                'id'    => $row['uuid'],
                'label' => $row['label'] . ($ip ? "\n" . $ip : ""), // Nome + IP
                'group' => $row['class'],
                'shape' => 'image',
                'image' => $this->getNodeImage($row['class'], $status),
                'size'  => 35,
                'font'  => ['size' => 11, 'face' => 'Plus Jakarta Sans', 'color' => '#1e293b', 'strokeWidth' => 2, 'strokeColor' => '#fff'],
                'ip'    => $ip,
                'real_name' => $row['label']
            ];
        }

        foreach ($this->db->table('glpi_plugin_solpi_inframap_edges')->get() as $row) {
            $edges[] = [
                'from' => $row['source_uuid'], 'to' => $row['target_uuid'],
                'arrows' => 'to', 'color' => '#cbd5e1', 'width' => 2
            ];
        }
        return ['nodes' => $nodes, 'edges' => $edges];
    }

    private function getNodeImage(string $class, string $status): string
    {
        $baseUrl = "https://img.icons8.com/fluency/96/";
        $suffix = ($status === 'OFFLINE') ? "?sepia=100" : "";

        return match($class) {
            'Router' => $baseUrl . "router.png" . $suffix,
            'Switch' => $baseUrl . "network-switch.png" . $suffix,
            'Mobile' => $baseUrl . "iphone.png" . $suffix,
            'Printer'=> $baseUrl . "print.png" . $suffix,
            'Server' => $baseUrl . "server.png" . $suffix,
            default  => $baseUrl . "monitor.png" . $suffix
        };
    }
}
