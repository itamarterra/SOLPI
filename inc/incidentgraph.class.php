<?php

declare(strict_types=1);

class PluginSolpiIncidentGraph extends CommonGLPI
{
    /**
     * Define o nome da aba que aparecerá no Ticket
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string|array
    {
        if ($item->getType() === 'Ticket') {
            return __('Incident Graph', 'solpi');
        }
        return '';
    }

    /**
     * Renderiza o conteúdo da aba
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if ($item->getType() === 'Ticket') {
            $ticketId = (int)$item->getID();
            self::showGraph($ticketId);
        }
        return true;
    }

    /**
     * Função interna para renderizar o container do Grafo
     */
    private static function showGraph(int $ticketId): void
    {
        echo "<div class='card mb-3'>";
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<h5 class='mb-0'><i class='ti ti-hierarchy-2 me-2'></i> SOLPI Relationship Explorer</h5>";
        echo "<button class='btn btn-sm btn-outline-primary' onclick='resetGraph()'><i class='ti ti-refresh me-1'></i> Reset View</button>";
        echo "</div>";
        echo "<div class='card-body' style='padding:0;'>";
        echo "<div id='incident-graph-container' style='width:100%; height:600px; background:#f8fafc;'></div>";
        echo "</div>";
        echo "</div>";

        // Scripts e Dados
        echo "<script src='https://unpkg.com/vis-network/standalone/umd/vis-network.min.js'></script>";
        echo "<script>
            function initGraph() {
                const container = document.getElementById('incident-graph-container');
                const url = '../plugins/solpi/ajax/incident_graph_data.php?tickets_id=$ticketId';

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        const options = {
                            nodes: {
                                shape: 'dot',
                                size: 20,
                                font: { size: 12, face: 'Inter' },
                                borderWidth: 2
                            },
                            edges: {
                                width: 2,
                                color: { inherit: 'from' },
                                smooth: { type: 'cubicBezier' }
                            },
                            groups: {
                                Ticket: { color: '#0d6efd' },
                                Computer: { color: '#10b981' },
                                User: { color: '#f59e0b' },
                                ZabbixAlert: { color: '#ef4444' }
                            },
                            physics: {
                                enabled: true,
                                barnesHut: { gravitationalConstant: -2000 }
                            }
                        };
                        window.solpi_network = new vis.Network(container, data, options);
                    });
            }

            function resetGraph() {
                if (window.solpi_network) window.solpi_network.fit();
            }

            document.addEventListener('DOMContentLoaded', initGraph);
            // Fallback para abas do GLPI que carregam via AJAX
            setTimeout(initGraph, 500);
        </script>";
    }
}
