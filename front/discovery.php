<?php

declare(strict_types=1);

/**
 * SOLPI Discovery Engine - Enterprise Center v10.8
 * Feature: Unified Visual Mapping & Comprehensive Side Actions (WhatsApp & Tickets)
 * Refined Layout: High-End Minimalist UI
 */

set_time_limit(0);
if (!defined('GLPI_ROOT')) define('GLPI_ROOT', '/var/www/glpi');
require_once GLPI_ROOT . '/inc/includes.php';
require_once GLPI_ROOT . '/plugins/solpi/vendor/autoload.php';

Session::checkRight('config', READ);
Html::header('Discovery Center', $_SERVER['PHP_SELF']);

$db = \SOLPI\Core\Database\DatabaseManager::getInstance();
$settingsRepo = new \SOLPI\Modules\Settings\SettingsRepository();
$infra = new \SOLPI\Modules\Infrastructure\Services\InfraManager();
$mapper = new \SOLPI\Modules\Topology\Services\L2Mapper();

$step = $_POST['step'] ?? 'form';
$results = []; $graphData = ['nodes' => [], 'edges' => []];
$msg = ''; $msgType = 'primary';

// Função auxiliar para padronizar ícones
$getIconUrl = function($type) {
    $baseUrl = "https://img.icons8.com/fluency/96/";
    return $baseUrl . match($type) {
        'Router' => "router.png", 'Switch' => "network-switch.png",
        'Mobile' => "iphone.png", 'Printer'=> "print.png",
        'Server' => "server.png", default  => "monitor.png"
    };
};

// --- LÓGICA DE AÇÕES ---
if (!empty($_POST['persisted_results'])) {
    $results = json_decode($_POST['persisted_results'], true);
}

if ($step === 'run' || $step === 'zabbix_sync') {
    $scanner = new \SOLPI\Modules\Discovery\Services\ScannerService();
    $results = ($step === 'run') ? $scanner->scanRange($_POST['start_ip'] ?? '', $_POST['end_ip'] ?? '') : $scanner->syncFromZabbix();
    $step = 'show_results';
}

if ($step === 'build_twin' && !empty($results)) {
    foreach ($results as $ip => $data) {
        $infra->registerAsset($data['type'], $data['name'], $data['external_id'] ?? null, array_merge($data, ['ip' => $ip, 'status' => 'ONLINE']));
    }
    $msg = "Digital Twin atualizado com sucesso! 🚀";
    $msgType = 'success';
    $step = 'show_results';
}

if ($step === 'save_settings') {
    $settingsRepo->upsert('core', 'director_phone', $_POST['director_phone'] ?? '');
    $msg = "Configurações de alerta atualizadas! ✅";
    $step = 'form';
}

if ($step === 'notify_director') {
    $proactiveService = new \SOLPI\Modules\Infrastructure\Services\InfraProactiveAlertService();
    $savedPhone = $settingsRepo->get('core', 'director_phone');
    $response = $proactiveService->sendExecutiveAlert($savedPhone);
    $msg = ($response['success'] ?? false) ? "Insight enviado ao WhatsApp! 📱" : "Erro no envio: " . ($response['message'] ?? '');
    $msgType = ($response['success'] ?? false) ? 'success' : 'danger';
    $step = 'show_results';
}

if ($step === 'simulate_failure') {
    $db->table('glpi_plugin_solpi_inframap_nodes')->where(['label' => 'SERVIDOR-CRITICO-TESTE'])->delete();
    $infra->registerAsset('Server', 'SERVIDOR-CRITICO-TESTE', 'TEST_INCIDENT', ['ip' => '240.0.0.1', 'status' => 'ONLINE']);
    $monitor = new \SOLPI\Modules\Infrastructure\Services\InfraHealthMonitor();
    $stats = $monitor->refreshGlobalStatus();
    $msg = ($stats['tickets_opened'] ?? 0) > 0 ? "Incidente detectado e chamado aberto! 🎫✅" : "Falha ao processar automação.";
    $msgType = 'success';
    $step = 'form';
}

if ($step === 'deep_map' && !empty($_POST['target_ip'])) {
    $ip = $_POST['target_ip'];
    $name = $_POST['target_name'] ?? $ip;
    $uuid = $infra->registerAsset('NetworkNode', $name, null, ['ip' => $ip, 'source' => 'DeepScan']);
    $mapper->mapNeighbors($ip, $uuid);
    $viz = new \SOLPI\Modules\Infrastructure\Services\InfraVisualizationService();
    $graphData = $viz->getGlobalMap();
    $step = 'show_map';
}

// PREPARAÇÃO DE DADOS DO MAPA
if (!empty($results) && $step === 'show_results') {
    foreach ($results as $ip => $data) {
        $graphData['nodes'][] = [
            'id' => $ip, 'label' => "<b>" . $data['name'] . "</b>\n" . $ip,
            'group' => $data['type'], 'shape' => 'image', 'image' => $getIconUrl($data['type']),
            'size' => 35, 'font' => ['multi' => 'html', 'face' => 'Plus Jakarta Sans'],
            'real_name' => $data['name'], 'ip' => $ip
        ];
    }
}

$counts = ['total' => $db->table('glpi_plugin_solpi_inframap_nodes')->count()];
$currentPhone = $settingsRepo->get('core', 'director_phone', '');
$csrf_token = Session::getNewCSRFToken();
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --sol-p: #4f46e5;
        --sol-p-light: #eef2ff;
        --sol-bg: #f8fafc;
        --sol-card: #ffffff;
        --sol-border: #e2e8f0;
        --sol-text: #1e293b;
        --sol-text-muted: #64748b;
        --sol-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        --sol-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.03), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
    }

    [data-theme="dark"] {
        --sol-bg: #0f172a;
        --sol-card: #1e293b;
        --sol-border: #334155;
        --sol-text: #f1f5f9;
        --sol-text-muted: #94a3b8;
        --sol-p-light: rgba(79, 70, 229, 0.1);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: var(--sol-bg) !important;
        color: var(--sol-text) !important;
        transition: background-color 0.4s ease, color 0.4s ease;
        margin: 0;
    }

    .sol-container { max-width: 1600px; margin: 0 auto; padding: 2rem; }

    /* Header */
    .sol-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
    .sol-header h1 { font-weight: 800; font-size: 2.2rem; letter-spacing: -1.5px; margin: 0; }

    /* Buttons */
    .btn-sol {
        border-radius: 14px; padding: 0.75rem 1.5rem; font-weight: 700;
        cursor: pointer; border: none; display: inline-flex; align-items: center;
        gap: 8px; font-size: 0.875rem; transition: all 0.2s ease;
    }
    .btn-p { background: var(--sol-p); color: #fff; }
    .btn-p:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 8px 15px rgba(79, 70, 229, 0.25); }
    .btn-s { background: #10b981; color: #fff; }
    .btn-s:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 8px 15px rgba(16, 185, 129, 0.25); }
    .btn-outline { background: transparent; border: 1.5px solid var(--sol-border); color: var(--sol-text); }
    .btn-outline:hover { background: var(--sol-p-light); border-color: var(--sol-p); color: var(--sol-p); }

    /* Cards */
    .sol-card {
        background: var(--sol-card); border-radius: 24px; border: 1px solid var(--sol-border);
        padding: 2rem; box-shadow: var(--sol-shadow); transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .sol-card:hover { box-shadow: var(--sol-shadow-lg); }

    /* Grid System */
    .sol-grid { display: grid; grid-template-columns: 360px 1fr 300px; gap: 1.5rem; height: calc(100vh - 200px); }

    /* Scrollable areas */
    .sol-scroll { overflow-y: auto; padding-right: 8px; }
    .sol-scroll::-webkit-scrollbar { width: 4px; }
    .sol-scroll::-webkit-scrollbar-thumb { background: var(--sol-border); border-radius: 10px; }

    /* Asset List */
    .sol-asset-card {
        background: var(--sol-card); padding: 1.1rem; border-radius: 18px;
        border: 1px solid var(--sol-border); margin-bottom: 0.75rem;
        cursor: pointer; transition: all 0.2s ease;
        display: flex; align-items: center; justify-content: space-between;
    }
    .sol-asset-card:hover { border-color: var(--sol-p); background: var(--sol-p-light); }
    .sol-asset-card.active { border-color: var(--sol-p); background: var(--sol-p-light); border-left: 5px solid var(--sol-p); }

    /* Viewport & Map */
    .sol-viewport {
        background: var(--sol-card); border-radius: 32px; border: 1px solid var(--sol-border);
        position: relative; overflow: hidden;
        background-image: radial-gradient(var(--sol-border) 1px, transparent 1px);
        background-size: 30px 30px;
    }
    #topology-map { width: 100%; height: 100%; }

    /* Detail Panel */
    #detail-card {
        position: absolute; bottom: 25px; left: 25px; width: 300px;
        background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px);
        border-radius: 20px; padding: 1.5rem; color: #fff; z-index: 1000;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); display: none;
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Form specific */
    .form-control {
        border-radius: 12px; border: 1.5px solid var(--sol-border);
        padding: 0.75rem 1rem; background: transparent; color: var(--sol-text);
        font-weight: 500; width: 100%; transition: border-color 0.2s;
    }
    .form-control:focus { border-color: var(--sol-p); outline: none; }

    .loader-ov {
        position: fixed; top:0; left:0; width:100%; height:100%;
        background: rgba(255,255,255,0.8); z-index:9999; display:none;
        align-items:center; justify-content:center; flex-direction: column;
        backdrop-filter: blur(5px);
    }
    [data-theme="dark"] .loader-ov { background: rgba(15, 23, 42, 0.8); }
</style>

<div id="sol-loader" class="loader-ov">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;"></div>
    <h5 class="mt-4 fw-bold">SOLPI Intelligent Engine</h5>
</div>

<div class="sol-container">
    <header class="sol-header">
        <h1 class="fw-bold">SOLPI <span style="color: var(--sol-p)">Discovery</span></h1>
        <div class="d-flex align-items-center gap-3">
            <div id="theme-btn" style="cursor:pointer; font-size: 1.25rem; color: var(--sol-text-muted);">
                <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
            </div>
            <a href="discovery.php" class="btn-sol btn-outline">NOVO SCAN</a>
        </div>
    </header>

    <?php if ($msg): ?>
        <div class="alert alert-<?=$msgType?> border-0 rounded-4 shadow-sm mb-4 p-3 fw-bold animate__animated animate__fadeIn">
            <i class="bi bi-check-circle-fill me-2"></i> <?=$msg?>
        </div>
    <?php endif; ?>

    <?php if ($step === 'form'): ?>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-7">
                <div class="sol-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">Varredura de Rede</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline rounded-pill px-3 fw-bold" style="font-size: 0.65rem;" onclick="setNet('192.168.21.1', '192.168.21.254')">FÍSICA</button>
                            <button type="button" class="btn btn-sm btn-outline rounded-pill px-3 fw-bold" style="font-size: 0.65rem;" onclick="setNet('172.20.0.1', '172.20.0.254')">DOCKER</button>
                        </div>
                    </div>
                    <form method="post" action="discovery.php" onsubmit="document.getElementById('sol-loader').style.display='flex'">
                        <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="step" value="run">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="small fw-bold opacity-50 mb-1">INÍCIO</label>
                                <input type="text" name="start_ip" id="sip" class="form-control" placeholder="0.0.0.0">
                            </div>
                            <div class="col-md-5">
                                <label class="small fw-bold opacity-50 mb-1">FIM</label>
                                <input type="text" name="end_ip" id="eip" class="form-control" placeholder="0.0.0.0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn-sol btn-p w-100">VARRER</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-5 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Sincronização Zabbix</h6>
                                <p class="text-muted small mb-0">Mapeia instantaneamente os hosts monitorados.</p>
                            </div>
                            <form method="post" action="discovery.php" onsubmit="document.getElementById('sol-loader').style.display='flex'">
                                <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="step" value="zabbix_sync">
                                <button type="submit" class="btn-sol btn-outline">SYNC AGORA</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sol-card mb-4">
                    <h5 class="fw-bold mb-3 text-success"><i class="bi bi-whatsapp"></i> Notificações</h5>
                    <form method="post" action="discovery.php">
                        <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="step" value="save_settings">
                        <label class="small fw-bold opacity-50 mb-1">WHATSAPP DIRETOR</label>
                        <input type="text" name="director_phone" class="form-control mb-3" placeholder="55..." value="<?= htmlspecialchars($currentPhone) ?>">
                        <button type="submit" class="btn-sol btn-outline w-100 btn-sm">Salvar Contato</button>
                    </form>
                </div>
                <div class="sol-card" style="background: var(--sol-bg); border-style: dashed; border-width: 2px;">
                    <h6 class="fw-bold text-danger mb-2">Simulador Técnico</h6>
                    <form method="post" action="discovery.php">
                        <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="step" value="simulate_failure">
                        <button type="submit" class="btn-sol w-100" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.75rem;">FORÇAR INCIDENTE</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="sol-grid">
            <div class="sol-scroll">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0 opacity-40 small text-uppercase">Detectados (<?= count($results) ?>)</h6>
                    <a href="discovery.php" class="btn btn-sm btn-outline rounded-pill px-3" style="font-size: 0.7rem;">← VOLTAR</a>
                </div>
                <?php foreach($results as $ip => $data): ?>
                    <div class="sol-asset-card" onclick="focusOnNode('<?= $ip ?>', this)">
                        <div><b class="d-block"><?= $data['name'] ?></b><code class="small fw-bold" style="color: var(--sol-p)"><?= $ip ?></code></div>
                        <i class="bi bi-arrow-right-short fs-4 opacity-30"></i>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sol-viewport">
                <div id="topology-map"></div>
                <div id="detail-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="badge bg-primary rounded-pill px-3" id="v-class">ATIVO</span>
                        <i class="bi bi-x-lg opacity-50" style="cursor:pointer" onclick="document.getElementById('detail-card').style.display='none'"></i>
                    </div>
                    <h4 class="fw-bold mb-1" id="v-name">Nome</h4>
                    <p class="text-white-50 mb-4" id="v-ip">0.0.0.0</p>
                    <button class="btn btn-primary w-100 fw-bold rounded-3 py-2" onclick="openGLPIFicha()">VER FICHA GLPI</button>
                </div>
            </div>

            <div class="sol-scroll">
                <div class="sol-card p-4 mb-4">
                    <h6 class="fw-bold mb-4 text-primary text-uppercase small" style="letter-spacing: 1px;">Comandos</h6>
                    <form method="post" action="discovery.php" class="mb-3">
                        <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="step" value="build_twin">
                        <input type="hidden" name="persisted_results" value='<?= htmlspecialchars(json_encode($results), ENT_QUOTES) ?>'>
                        <button type="submit" class="btn-sol btn-s w-100">SALVAR TWIN</button>
                    </form>
                    <form method="post" action="discovery.php" onsubmit="document.getElementById('sol-loader').style.display='flex'">
                        <input type="hidden" name="_glpi_csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="step" value="notify_director">
                        <input type="hidden" name="persisted_results" value='<?= htmlspecialchars(json_encode($results), ENT_QUOTES) ?>'>
                        <button type="submit" class="btn-sol btn-p w-100">REPORT IA</button>
                    </form>
                </div>
                <div class="p-4 rounded-4" style="background: var(--sol-p-light); border: 1px solid var(--sol-p);">
                    <div class="small fw-bold text-primary mb-1">SOLPI INSIGHT</div>
                    <p class="small text-muted mb-0">Use o <b>Report IA</b> para gerar uma análise proativa e enviar via WhatsApp.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    const themeBtn = document.getElementById('theme-btn');
    const themeIcon = document.getElementById('theme-icon');
    function setTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('sol-theme', t);
        themeIcon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
    themeBtn?.addEventListener('click', () => {
        const c = document.documentElement.getAttribute('data-theme');
        setTheme(c === 'dark' ? 'light' : 'dark');
        if(typeof network !== 'undefined') loadMap();
    });
    setTheme(localStorage.getItem('sol-theme') || 'light');

    let network, nodesData, currentSelectedIp;
    <?php if (!empty($graphData['nodes'])): ?>
    function loadMap() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const container = document.getElementById('topology-map');
        const data = <?= json_encode($graphData) ?>; nodesData = data.nodes;
        const options = {
            nodes: {
                font: { face: 'Plus Jakarta Sans', color: isDark ? '#f1f5f9' : '#1e293b', size: 12 },
                borderWidth: 2, shadow: { enabled: true, color: 'rgba(0,0,0,0.05)' }
            },
            edges: { width: 2, color: isDark ? '#334155' : '#e2e8f0', smooth: { type: 'cubicBezier' }, arrows: 'to' },
            physics: { enabled: true, barnesHut: { gravitationalConstant: -3000 } }
        };
        network = new vis.Network(container, data, options);
        network.on("click", (p) => { if(p.nodes.length) showFloatingPanel(nodesData.find(n => n.id === p.nodes[0])); });
    }
    loadMap();
    <?php endif; ?>

    function focusOnNode(ip, el) {
        document.querySelectorAll('.sol-asset-card').forEach(c => c.classList.remove('active')); el.classList.add('active');
        const t = nodesData.find(n => n.id === ip || n.ip === ip);
        if(t && network) { network.focus(t.id, { scale: 1.4, animation: { duration: 1000 } }); network.selectNodes([t.id]); showFloatingPanel(t); }
    }
    function showFloatingPanel(n) { currentSelectedIp = n.ip; document.getElementById('v-name').innerText = n.real_name || n.label; document.getElementById('v-ip').innerText = n.ip || 'N/A'; document.getElementById('v-class').innerText = n.group.toUpperCase(); document.getElementById('detail-card').style.display = 'block'; }
    function openGLPIFicha() { if(currentSelectedIp) window.open('../../../front/search.php?globalsearch=' + currentSelectedIp, '_blank'); }
    function setNet(s, e) { document.getElementById('sip').value = s; document.getElementById('eip').value = e; }
</script>

<?php Html::footer(); ?>
