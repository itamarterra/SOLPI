<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

require_once __DIR__ . '/../inc/bootstrap.php';

use SOLPI\Integrations\Evolution\EvolutionClient;
use SOLPI\Core\Config;

// ------------------------------------------------------------------
// Carregar configuração da Evolution API
// ------------------------------------------------------------------
$config = new Config();
$config->load();
$evolutionConfig = $config->get('evolution', []);
$client = new EvolutionClient($evolutionConfig);

$successMsg = '';
$errorMsg   = '';

// ------------------------------------------------------------------
// POST: enviar mensagem de teste
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {

    $number  = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($number === '' || $message === '') {
        $errorMsg = 'Número e mensagem são obrigatórios.';
    } else {
        $result = $client->sendText($number, $message);
        if ($result['success'] ?? false) {
            $successMsg = "Mensagem enviada para +{$number}!";
        } else {
            $errorMsg = 'Falha ao enviar: ' . ($result['error'] ?? json_encode($result));
        }
    }
}

// ------------------------------------------------------------------
// Buscar status atual da instância
// ------------------------------------------------------------------
$instance = $client->fetchInstance();
$isConnected = ($instance['connectionStatus'] ?? '') === 'open';
$ownerJid    = $instance['ownerJid'] ?? null;
$phone       = $ownerJid ? preg_replace('/@.*/', '', $ownerJid) : null;
$profilePic  = $instance['profilePicUrl'] ?? null;
$msgCount    = $instance['_count']['Message'] ?? 0;
$contactCount = $instance['_count']['Contact'] ?? 0;

Html::header('WhatsApp — SOLPI', '', 'central');
?>

<div class="container-fluid py-3">

    <div class="d-flex align-items-center mb-4 gap-3">
        <?php if ($profilePic): ?>
            <img src="<?= htmlspecialchars($profilePic) ?>" width="56" height="56"
                 class="rounded-circle border" alt="Foto WhatsApp">
        <?php endif; ?>
        <div>
            <h3 class="mb-0">WhatsApp — Evolution API</h3>
            <small class="text-muted">Instância: <strong>solpi</strong></small>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- STATUS -->
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-1"><?= $isConnected ? '✅' : '❌' ?></div>
                    <div class="fw-bold mt-1"><?= $isConnected ? 'Conectado' : 'Desconectado' ?></div>
                    <?php if ($phone): ?>
                        <small class="text-muted">+<?= htmlspecialchars($phone) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-primary"><?= number_format($msgCount) ?></div>
                    <div class="text-muted">Mensagens</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-success"><?= number_format($contactCount) ?></div>
                    <div class="text-muted">Contatos</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-info">
                        <?= $instance['_count']['Chat'] ?? 0 ?>
                    </div>
                    <div class="text-muted">Chats</div>
                </div>
            </div>
        </div>

    </div>

    <?php if (!$isConnected): ?>
    <!-- QR CODE (quando desconectado) -->
    <div class="card mb-4">
        <div class="card-header fw-bold">Conectar WhatsApp</div>
        <div class="card-body text-center">
            <?php
            $qr = $client->connect();
            if (!empty($qr['base64'])):
            ?>
                <p class="text-muted mb-3">Abra o WhatsApp → Dispositivos Conectados → Conectar Dispositivo</p>
                <img src="<?= htmlspecialchars($qr['base64']) ?>" width="250" height="250" alt="QR Code">
            <?php else: ?>
                <p class="text-warning">Não foi possível gerar o QR code. Verifique a Evolution API.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ENVIAR MENSAGEM DE TESTE -->
    <?php if ($isConnected): ?>
    <div class="card mb-4">
        <div class="card-header fw-bold">Enviar Mensagem de Teste</div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Número (somente dígitos, com DDI)</label>
                    <input type="text" name="phone" class="form-control" style="max-width:300px"
                           placeholder="5519981584722" required>
                    <small class="text-muted">Ex: 5511999999999 (DDI + DDD + número)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensagem</label>
                    <textarea name="message" class="form-control" rows="3" style="max-width:500px"
                              required>Olá! Esta é uma mensagem de teste do SOLPI.</textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-success">
                    📤 Enviar via WhatsApp
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- INFO DA API -->
    <div class="card">
        <div class="card-header fw-bold">Configuração da Integração</div>
        <div class="card-body">
            <table class="table table-sm table-bordered" style="max-width:500px">
                <tr><th>URL da API</th><td><?= htmlspecialchars($evolutionConfig['base_url'] ?? '-') ?></td></tr>
                <tr><th>Instância</th><td><?= htmlspecialchars($evolutionConfig['instance'] ?? '-') ?></td></tr>
                <tr><th>Status</th><td><?= $isConnected ? '<span class="badge bg-success">ONLINE</span>' : '<span class="badge bg-danger">OFFLINE</span>' ?></td></tr>
            </table>
        </div>
    </div>

</div>

<?php Html::footer(); ?>