<?php

declare(strict_types=1);

/**
 * SOLPI Agent Dashboard - Aurora v1.0
 */

include('../../../inc/includes.php');
require_once GLPI_ROOT . '/plugins/solpi/vendor/autoload.php';

Session::checkRight('config', READ);
Html::header('Agent Management', $_SERVER['PHP_SELF']);

$db = \SOLPI\Core\Database\DatabaseManager::getInstance();
$agents = $db->table('glpi_plugin_solpi_installations')->get();
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root { --sol-p: #4f46e5; --sol-bg: #f8fafc; --sol-card: #ffffff; --sol-border: #e2e8f0; --sol-text: #1e293b; }
    [data-theme="dark"] { --sol-bg: #0f172a; --sol-card: #1e293b; --sol-border: #334155; --sol-text: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: var(--sol-bg) !important; color: var(--sol-text) !important; transition: 0.4s; margin: 0; }
    .sol-container { max-width: 1400px; margin: 0 auto; padding: 2.5rem; }
    .sol-card { background: var(--sol-card); border-radius: 28px; border: 1px solid var(--sol-border); padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.02); }
    .status-pill { padding: 6px 14px; border-radius: 100px; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-online { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .agent-row { border-bottom: 1px solid var(--sol-border); transition: 0.2s; }
    .agent-row:hover { background: rgba(79, 70, 229, 0.02); }
</style>

<div class="sol-container">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold m-0" style="letter-spacing: -1.5px;">SOLPI <span style="color: var(--sol-p)">Agents</span></h1>
            <p class="text-muted small mb-0">Gestão e monitoramento de agentes remotos.</p>
        </div>
        <div class="d-flex gap-3">
             <div id="theme-btn" style="cursor:pointer; font-size: 1.5rem;"><i class="bi bi-moon-stars-fill" id="theme-icon"></i></div>
        </div>
    </header>

    <div class="sol-card">
        <div class="table-responsive">
            <table class="table table-borderless align-middle m-0">
                <thead>
                    <tr class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">
                        <th>Identificação</th>
                        <th>Versão</th>
                        <th>IP / Endereço</th>
                        <th>Status</th>
                        <th>Último Sinal</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agents)): ?>
                        <tr><td colspan="6" class="text-center py-5 opacity-50">Nenhum agente registrado no momento.</td></tr>
                    <?php endif; ?>
                    <?php foreach($agents as $agent): ?>
                    <tr class="agent-row">
                        <td class="py-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3"><i class="bi bi-pc-display text-primary"></i></div>
                                <div><b class="d-block"><?= $agent['site_name'] ?></b><span class="small text-muted">ID: #<?= $agent['id'] ?></span></div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border fw-bold"><?= $agent['solpi_version'] ?: '1.0' ?></span></td>
                        <td><code><?= $agent['ip_address'] ?: 'N/A' ?></code></td>
                        <td><span class="status-pill status-online">ONLINE</span></td>
                        <td class="small"><?= date('H:i:s d/m/Y', strtotime($agent['last_seen'])) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">DETALHES</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const themeBtn = document.getElementById('theme-btn');
    function setTheme(t) { document.documentElement.setAttribute('data-theme', t); localStorage.setItem('sol-theme', t); document.getElementById('theme-icon').className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill'; }
    themeBtn?.addEventListener('click', () => { const c = document.documentElement.getAttribute('data-theme'); setTheme(c === 'dark' ? 'light' : 'dark'); });
    setTheme(localStorage.getItem('sol-theme') || 'light');
</script>

<?php Html::footer(); ?>
