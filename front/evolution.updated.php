<?php

declare(strict_types=1);

use SOLPI\Integrations\Evolution\EvolutionService;

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

$service = new EvolutionService();
$instance = $service->fetchInstance();
$status = [
    'enabled' => true,
    'connected' => $service->isConnected(),
    'connectionStatus' => (string)($instance['connectionStatus'] ?? ''),
];
$session = [
    'instance' => $instance,
];
$qrCode = $service->connect();

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>Evolution API</h1>

        <div class="card mb-3">
            <div class="card-header">Status do serviço</div>
            <div class="card-body">
                <pre><?= htmlspecialchars(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Sessão</div>
            <div class="card-body">
                <pre><?= htmlspecialchars(json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">QR Code</div>
            <div class="card-body">
                <?php
                $image = null;
                if (!empty($qrCode['image'])) {
                    $image = $qrCode['image'];
                } elseif (!empty($qrCode['qrcode'])) {
                    $image = $qrCode['qrcode'];
                } elseif (!empty($qrCode['qr_code'])) {
                    $image = $qrCode['qr_code'];
                } elseif (!empty($qrCode['qr'])) {
                    $image = $qrCode['qr'];
                }

                if ($image !== null && strpos($image, 'data:image') !== 0) {
                    $image = 'data:image/png;base64,' . $image;
                }
                ?>

                <?php if (!empty($image)): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="QR Code" class="img-fluid" />
                <?php else: ?>
                    <div class="alert alert-warning">QR Code não disponível. Verifique o serviço Evolution e a API.</div>
                    <pre><?= htmlspecialchars(json_encode($qrCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';
