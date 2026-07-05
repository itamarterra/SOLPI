<?php

declare(strict_types=1);

/**
 * SOLPI Infrastructure Explorer
 * Visualizador Interativo do Digital Twin
 */

include __DIR__ . '/../inc/includes.php';

// Segurança GLPI
Session::checkRight('config', READ);

Html::header('SOLPI Infrastructure Explorer', $_SERVER['PHP_SELF'], 'config', 'plugin_solpi_infra');

?>
<div class="container-fluid" style="padding: 20px;">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold"><i class="ti ti-topology-complex me-2"></i> Infrastructure Digital Twin</h4>
            <div class="btn-group">
                <button class="btn btn-sm btn-light" onclick="network.fit()"><i class="ti ti-maximize me-1"></i> Centralizar</button>
                <button class="btn btn-sm btn-light" onclick="loadMap()"><i class="ti ti-refresh me-1"></i> Recarregar</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="infra-map-container" style="width: 100%; height: 750px; background: #fdfdfd;">
                <div id="loader" class="d-flex flex-column align-items-center justify-content-center h-100">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted fw-bold">Mapeando Topologia Enterprise...</p>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light py-2 px-4 d-flex justify-content-center gap-4">
            <span class="small fw-bold"><span style="color:#3b82f6;">●</span> Link Físico</span>
            <span class="small fw-bold"><span style="color:#ef4444;">●</span> Dependência</span>
            <span class="small fw-bold"><span style="color:#10b981;">●</span> Suporte de Serviço</span>
            <span class="small fw-bold"><span style="color:#8b5cf6;">●</span> Executa em (VM/Docker)</span>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    let network = null;

    function loadMap() {
        const container = document.getElementById('infra-map-container');
        const loader = document.getElementById('loader');

        loader.style.display = 'flex';

        fetch('../ajax/infra_map_data.php')
            .then(r => r.json())
            .then(data => {
                const options = {
                    nodes: {
                        shape: 'dot',
                        size: 25,
                        font: { size: 13, face: 'Plus Jakarta Sans, Inter' },
                        borderWidth: 2,
                        shadow: true
                    },
                    edges: {
                        width: 2,
                        smooth: { type: 'continuous' },
                        font: { size: 10, align: 'middle' }
                    },
                    groups: {
                        Asset: { color: '#6366f1' },
                        Computer: { color: '#3b82f6' },
                        Server: { color: '#1d4ed8' },
                        Switch: { color: '#0ea5e9' },
                        User: { color: '#f59e0b' },
                        NetworkNode: { color: '#64748b' }
                    },
                    physics: {
                        enabled: true,
                        solver: 'forceAtlas2Based',
                        forceAtlas2Based: {
                            gravitationalConstant: -100,
                            centralGravity: 0.01,
                            springLength: 150
                        },
                        stabilization: { iterations: 150 }
                    }
                };

                network = new vis.Network(container, data, options);

                network.on("stabilizationIterationsDone", function () {
                    loader.style.display = 'none';
                });
            })
            .catch(err => {
                container.innerHTML = `<div class='alert alert-danger m-5'>Erro ao carregar o Digital Twin: ${err.message}</div>`;
            });
    }

    document.addEventListener('DOMContentLoaded', loadMap);
</script>

<?php
Html::footer();
