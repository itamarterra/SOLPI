<?php

declare(strict_types=1);

/**
 * SOLPI Discovery Center
 * Trigger and monitor network scans
 */

include __DIR__ . '/../inc/includes.php';

// Security
Session::checkRight('config', UPDATE);

Html::header('SOLPI Discovery Center', $_SERVER['PHP_SELF'], 'config', 'plugin_solpi_discovery');

$step = $_POST['step'] ?? 'form';
$results = [];

if ($step === 'run') {
    $startIp = $_POST['start_ip'] ?? '';
    $endIp = $_POST['end_ip'] ?? '';

    if (filter_var($startIp, FILTER_VALIDATE_IP) && filter_var($endIp, FILTER_VALIDATE_IP)) {
        $scanner = new \SOLPI\Modules\Discovery\Services\ScannerService();
        $results = $scanner->scanRange($startIp, $endIp);
    }
}

?>

<div class="container" style="max-width: 800px; margin-top: 30px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-dark text-white p-4">
            <h4 class="mb-0 fw-bold"><i class="ti ti-search me-2"></i> Network Discovery Engine</h4>
            <p class="small mb-0 opacity-75">Varra sua rede para alimentar o Digital Twin</p>
        </div>

        <div class="card-body p-4">
            <?php if ($step === 'form'): ?>
                <form method="post" action="discovery.php" onsubmit="showLoader()">
                    <input type="hidden" name="step" value="run">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">IP Inicial</label>
                            <input type="text" name="start_ip" class="form-control" placeholder="ex: 192.168.1.1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">IP Final</label>
                            <input type="text" name="end_ip" class="form-control" placeholder="ex: 192.168.1.254" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="ti ti-broadcast me-2"></i> Iniciar Varredura de Rede
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="ti ti-check fs-4 me-2"></i>
                    <div>Varredura concluída! <b><?= count($results) ?></b> dispositivos identificados e integrados ao Grafo.</div>
                </div>

                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>IP</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Protocolo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Nenhum dispositivo encontrado nesta faixa.</td></tr>
                            <?php else: ?>
                                <?php foreach ($results as $ip => $data): ?>
                                    <tr>
                                        <td><code><?= $ip ?></code></td>
                                        <td><strong><?= htmlspecialchars($data['name'] ?? 'N/A') ?></strong></td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info"><?= $data['type'] ?? 'Asset' ?></span></td>
                                        <td><span class="badge bg-secondary"><?= $data['protocol'] ?? '?' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="discovery.php" class="btn btn-outline-secondary">Voltar</a>
                    <a href="infrastructure.php" class="btn btn-success px-4">Ver Mapa Visual <i class="ti ti-arrow-right ms-1"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="scan-loader" style="position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.9); z-index:9999; display:none; align-items:center; justify-content:center; flex-direction: column;">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;"></div>
    <h5 class="mt-3 fw-bold">SOLPI está explorando sua rede...</h5>
    <p class="text-muted small">Extraindo dados técnicos via SNMP e ICMP</p>
</div>

<script>
    function showLoader() {
        document.getElementById('scan-loader').style.display = 'flex';
    }
</script>

<?php
Html::footer();
