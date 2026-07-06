<?php

declare(strict_types=1);

/**
 * SOLPI Infrastructure Explorer - Enterprise v4.0
 * Ultra-Clean Topology Interface
 */

include __DIR__ . '/../inc/includes.php';

// Segurança GLPI
Session::checkRight('config', READ);

Html::header('Infrastructure Explorer', $_SERVER['PHP_SELF']);

// Ação manual de saúde
if (isset($_POST['refresh_health'])) {
    $monitor = new \SOLPI\Modules\Infrastructure\Services\InfraHealthMonitor();
    $monitor->refreshGlobalStatus();
}

$snapshotRepo = new \SOLPI\Modules\DigitalTwin\Repositories\SnapshotRepository();
$latestSnapshot = $snapshotRepo->findLatest();
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root { --sol-p: #4f46e5; --sol-bg: #f8fafc; --sol-b: #e2e8f0; --sol-t: #1e293b; }
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: var(--sol-bg) !important; margin: 0; overflow: hidden; }

    .sol-explorer { display: flex; height: calc(100vh - 100px); background: #fff; }

    /* Sidebar Minimal */
    .sol-aside { width: 350px; background: #fff; border-right: 1px solid var(--sol-b); display: flex; flex-direction: column; padding: 2.5rem; box-shadow: 10px 0 30px rgba(0,0,0,0.01); }
    .sol-aside h2 { font-weight: 800; font-size: 1.4rem; letter-spacing: -1px; margin-bottom: 2.5rem; }

    /* Search & Filters */
    .sol-search { background: #f1f5f9; border: none; border-radius: 16px; padding: 1rem 1.25rem; font-weight: 500; width: 100%; margin-bottom: 2rem; }
    .sol-filter-h { font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; display: block; }

    .sol-type-btn { display: flex; align-items: center; gap: 12px; padding: 0.75rem 1rem; border-radius: 12px; cursor: pointer; transition: 0.2s; font-weight: 600; font-size: 0.9rem; color: #475569; }
    .sol-type-btn:hover { background: #f1f5f9; color: var(--sol-p); }
    .sol-type-btn i { font-size: 1.2rem; }

    /* Map Area */
    .sol-main { flex-grow: 1; position: relative; background: #fcfcfc; }
    #infra-map-container { width: 100%; height: 100%; }

    /* Floating Legend */
    .sol-legend { position: absolute; top: 30px; left: 30px; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--sol-b); box-shadow: 0 10px 20px rgba(0,0,0,0.03); z-index: 10; font-size: 0.75rem; font-weight: 700; }
    .sol-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }

    .sol-controls { position: absolute; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 10px; z-index: 10; }
    .btn-sol-ctrl { width: 50px; height: 50px; background: #fff; border: 1px solid var(--sol-b); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; }
    .btn-sol-ctrl:hover { background: var(--sol-p); color: #fff; border-color: var(--sol-p); transform: translateY(-3px); }

    .sol-loader { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; }
</style>

<div class="sol-explorer">

    <aside class="sol-aside">
        <h2>SOLPI <span style="color: var(--sol-p)">Explorer</span></h2>

        <input type="text" id="node-search" class="sol-search" placeholder="Localizar ativo ou IP...">

        <div class="mb-5">
            <span class="sol-filter-h">Monitoramento Ativo</span>
            <form method="post">
                <?php Html::generateFormToken(); ?>
                <button type="submit" name="refresh_health" class="btn-sol-ctrl w-100" style="height: auto; padding: 0.75rem; font-size: 0.85rem; font-weight: 800; border-radius: 12px;">
                    <i class="bi bi-heart-pulse me-2"></i> ATUALIZAR SAÚDE
                </button>
            </form>
        </div>

        <div>
            <span class="sol-filter-h">Camadas da Rede</span>
            <div class="sol-type-btn" onclick="filterType('Computer')"><i class="bi bi-pc-display text-primary"></i> Estações de Trabalho</div>
            <div class="sol-type-btn" onclick="filterType('Server')"><i class="bi bi-server text-success"></i> Servidores Críticos</div>
            <div class="sol-type-btn" onclick="filterType('Switch')"><i class="bi bi-hdd-network text-info"></i> Infraestrutura de Rede</div>
        </div>

        <div class="mt-auto p-4 bg-light rounded-4" style="background: #f1f5f9;">
            <span class="sol-filter-h">Última Foto do Twin</span>
            <div class="fw-bold small"><?= $latestSnapshot ? $latestSnapshot->name() : 'Snapshot Gerado' ?></div>
            <div class="text-muted" style="font-size: 0.7rem;"><?= $latestSnapshot ? date('d/m/Y H:i', strtotime($latestSnapshot->createdAt())) : 'Primeira varredura necessária' ?></div>
        </div>
    </aside>

    <main class="sol-main">
        <div id="loader" class="sol-loader">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <h5 class="mt-4 fw-bold">Construindo Digital Twin...</h5>
        </div>

        <div class="sol-legend">
            <div class="mb-2"><span class="sol-status-dot" style="background: #10b981;"></span> ATIVOS ONLINE</div>
            <div class="mb-3"><span class="sol-status-dot" style="background: #ef4444;"></span> ATIVOS OFFLINE</div>
            <div style="color: var(--sol-p)">━━ LINK FÍSICO (VLAN)</div>
            <div style="color: #ef4444">━━ DEPENDÊNCIA CRÍTICA</div>
        </div>

        <div id="infra-map-container"></div>

        <div class="sol-controls">
            <div class="btn-sol-ctrl" onclick="network.fit()" title="Centralizar"><i class="bi bi-fullscreen"></i></div>
            <div class="btn-sol-ctrl" onclick="loadMap()" title="Recarregar"><i class="bi bi-arrow-clockwise"></i></div>
        </div>
    </main>

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
                        shape: 'dot', size: 30,
                        font: { size: 13, face: 'Plus Jakarta Sans', color: '#1e293b' },
                        borderWidth: 2,
                        shadow: { enabled: true, color: 'rgba(0,0,0,0.03)', size: 10, x: 3, y: 3 }
                    },
                    edges: { width: 2, smooth: { type: 'cubicBezier' }, font: { size: 9, background: '#ffffff' } },
                    physics: { enabled: true, solver: 'forceAtlas2Based', stabilization: { iterations: 150 } }
                };
                network = new vis.Network(container, data, options);
                network.on("stabilizationIterationsDone", () => loader.style.display = 'none');
            });
    }

    document.addEventListener('DOMContentLoaded', loadMap);
</script>

<?php Html::footer(); ?>
