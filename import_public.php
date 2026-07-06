<?php

declare(strict_types=1);

/**
 * SOLPI - Analisador Universal de Dados
 * Versão 11.0 Cognitive - Mult-Intent Processing (Users, Tickets, Assets)
 */

$root = dirname(__DIR__);
if (!file_exists($root . '/inc/includes.php')) { $root = '/var/www/glpi'; }
require_once $root . '/vendor/autoload.php';
if (class_exists('Glpi\\Kernel\\Kernel')) { (new \Glpi\Kernel\Kernel())->boot(); }
require_once $root . '/inc/includes.php';

$loader = $root . '/plugins/solpi/vendor/autoload.php';
if (file_exists($loader)) { require_once $loader; }

if (Session::getLoginUserID() === false) { exit; }

use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;
use SOLPI\Knowledge\Services\CognitiveIntentService;

$uploadDir = $root . "/files/_tmp/solpi_import/";
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

// --- Funções Auxiliares ---
function solpi_get_id_by_name($itemtype, $name) {
    global $DB; if (!$name) return 0;
    $table = getTableForItemType($itemtype);
    $iterator = $DB->request(['FROM' => $table, 'WHERE' => ['name' => $name], 'LIMIT' => 1]);
    if (count($iterator)) return (int)$iterator->current()['id'];
    $item = new $itemtype();
    $input = ['name' => $name];
    if ($itemtype === 'Entity') $input['entities_id'] = 0;
    return (int)$item->add($input);
}

$self = $_SERVER['PHP_SELF'];
$step = $_GET['step'] ?? 'upload';
$msg = ''; $msgType = 'success'; $rows = []; $headers = []; $mapping = []; $tmpFile = ''; $detectedIntent = 'ticket';

// AÇÃO: EXECUTAR IMPORTAÇÃO
if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mapping = array_flip(array_filter($_POST['mapping'] ?? []));
    $intent = $_POST['intent'] ?? 'ticket';
    $targetFile = $_POST['tmp_file'] ?? '';

    if ($targetFile && file_exists($targetFile)) {
        $rows = (str_contains($targetFile, 'xl')) ? (new ExcelParser())->parse($targetFile) : [];
        $count = 0;

        foreach ($rows as $r) {
            try {
                if ($intent === 'user') {
                    $user = new User();
                    $login = $r[$mapping['login'] ?? ''] ?? $r[$mapping['nome'] ?? ''] ?? '';
                    if (!$login) continue;
                    $userId = $user->add([
                        'name' => $login, 'firstname' => $r[$mapping['nome'] ?? ''] ?? '',
                        'realname' => $r[$mapping['sobrenome'] ?? ''] ?? '',
                        'is_active' => 1, '_password' => $r[$mapping['senha'] ?? ''] ?? '123456',
                    ]);
                    if ($userId) {
                        if ($email = ($r[$mapping['email'] ?? ''] ?? '')) (new UserEmail())->add(['users_id' => $userId, 'email' => $email, 'is_default' => 1]);
                        if ($entity = ($r[$mapping['empresa'] ?? ''] ?? '')) {
                            $eid = solpi_get_id_by_name('Entity', $entity);
                            (new \Profile_User())->add(['users_id' => $userId, 'entities_id' => $eid, 'profiles_id' => 1]);
                        }
                        $count++;
                    }
                } elseif ($intent === 'asset') {
                    // Importação Universal de Ativos (Mapeado para Computer por padrão)
                    $comp = new Computer();
                    $name = $r[$mapping['nome'] ?? ''] ?? $r[$mapping['serie'] ?? ''] ?? 'Ativo s/ Nome';
                    if (!$name) continue;
                    $compId = $comp->add([
                        'name' => $name,
                        'serial' => $r[$mapping['serie'] ?? ''] ?? '',
                        'otherserial' => $r[$mapping['patrimonio'] ?? ''] ?? '',
                        'comment' => "Importado via SOLPI Analyzer em " . date('d/m/Y'),
                        'entities_id' => $_SESSION['glpiactive_entity'] ?? 0
                    ]);
                    if ($compId) $count++;

                } else {
                    $ticket = new Ticket();
                    $desc = $r[$mapping['problema'] ?? ''] ?? '';
                    if (!$desc) continue;
                    if ($ticket->add([
                        'name' => mb_strimwidth($desc, 0, 70, '...'),
                        'content' => $desc . "\n\n(Importado via SOLPI)",
                        'entities_id' => $_SESSION['glpiactive_entity'] ?? 0,
                        'requesttypes_id' => 1,
                        '_users_id_requester' => Session::getLoginUserID()
                    ])) $count++;
                }
            } catch (Throwable) { continue; }
        }
        @unlink($targetFile);
        $msg = "Sucesso! $count registros processados."; $step = 'done';
    }
}

// AÇÃO: ANALISAR
if ($step === 'preview') {
    $input = $_POST['paste_data'] ?? ''; $file = $_FILES['source_file'] ?? null;
    if ($input || ($file && $file['error'] === 0)) {
        $tmpFile = $uploadDir . uniqid('sp_') . ($input ? '.txt' : '.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        file_put_contents($tmpFile, $input ?: file_get_contents($file['tmp_name']));
        $rows = (new ExcelParser())->parse($tmpFile);
        if ($rows) {
            $headers = array_keys($rows[0]);
            $analysis = (new CognitiveIntentService())->detect($headers, $rows);
            $detectedIntent = $analysis['intent'];
            $mapping = (new ColumnDetector())->detect($headers);
        }
    }
}

Html::header("SOLPI Import", $self);
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root { --sol-p: #4f46e5; --sol-bg: #f8fafc; --sol-card: #ffffff; --sol-border: #e2e8f0; --sol-text: #1e293b; }
    [data-theme="dark"] { --sol-bg: #0f172a; --sol-card: #1e293b; --sol-border: #334155; --sol-text: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: var(--sol-bg) !important; color: var(--sol-text) !important; transition: 0.4s; margin: 0; }
    .sol-wrap { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
    .sol-card { background: var(--sol-card); border-radius: 32px; border: 1px solid var(--sol-border); padding: 2.5rem; box-shadow: 0 15px 30px rgba(0,0,0,0.03); }
    .theme-toggle { background: var(--sol-card); border: 1px solid var(--sol-border); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; }

    .sol-stepper { display: flex; justify-content: space-between; margin-bottom: 4rem; position: relative; }
    .sol-stepper::before { content: ''; position: absolute; top: 1.2rem; left: 10%; width: 80%; height: 2px; background: var(--sol-border); z-index: 1; }
    .step-item { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; width: 33.33%; }
    .step-dot { width: 2.5rem; height: 2.5rem; background: var(--sol-card); border: 2px solid var(--sol-border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #94a3b8; }
    .step-item.active .step-dot { border-color: var(--sol-p); background: var(--sol-p); color: #fff; box-shadow: 0 0 0 6px rgba(79, 70, 229, 0.1); }
    .step-item.completed .step-dot { border-color: #10b981; background: #10b981; color: #fff; }

    .intent-chip { padding: 12px 24px; border-radius: 14px; background: var(--sol-bg); border: 2px solid transparent; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: 0.3s; color: #94a3b8; opacity: 0.5; }
    .intent-active { border-color: var(--sol-p); background: rgba(79, 70, 229, 0.1); color: var(--sol-p); opacity: 1; }

    .form-control, .form-select { border-radius: 16px; border: 2px solid var(--sol-border); background: transparent; color: var(--sol-text); padding: 0.8rem 1.2rem; font-weight: 500; }
    .btn-p { background: var(--sol-p); color: #fff; border-radius: 16px; padding: 1rem 2rem; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; }
</style>

<div class="sol-wrap">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold m-0" style="letter-spacing: -1.5px;">SOLPI <span style="color:var(--sol-p)">IMPORT</span></h2>
        <div id="theme-btn" class="theme-toggle"><i class="bi bi-moon-stars-fill" id="theme-icon"></i></div>
    </header>

    <div class="sol-stepper">
        <div class="step-item <?=($step==='upload'?'active':($step==='preview'||$step==='done'?'completed':''))?>">
            <div class="step-dot"><?=($step==='preview'||$step==='done'?'<i class="bi bi-check-lg"></i>':'1')?></div>
            <div class="step-label small fw-bold">CARREGAR</div>
        </div>
        <div class="step-item <?=($step==='preview'?'active':($step==='done'?'completed':''))?>">
            <div class="step-dot"><?=($step==='done'?'<i class="bi bi-check-lg"></i>':'2')?></div>
            <div class="step-label small fw-bold">ANALISAR</div>
        </div>
        <div class="step-item <?=($step==='done'?'active':'')?>">
            <div class="step-dot">3</div>
            <div class="step-label small fw-bold">FINALIZAR</div>
        </div>
    </div>

    <?php if ($msg): ?><div class="sol-card mb-5 fw-bold text-success" style="background: rgba(16, 185, 129, 0.1);"><i class="bi bi-check-circle-fill me-2"></i> <?=$msg?></div><?php endif; ?>

    <?php if ($step === 'done'): ?>
        <div class="sol-card text-center py-5">
            <h2 class="fw-bold mb-5">Processamento Cognitivo Concluído</h2>
            <a href="<?= $self ?>" class="btn-p">NOVA IMPORTAÇÃO</a>
        </div>

    <?php elseif ($step === 'preview' && $rows): ?>
        <form method="post" action="<?= $self ?>?step=import">
            <input type="hidden" name="tmp_file" value="<?=$tmpFile?>">
            <input type="hidden" name="intent" id="intent_field" value="<?=$detectedIntent?>">

            <div class="sol-card">
                <h5 class="fw-bold mb-4">Intenção Identificada pela IA:</h5>
                <div class="d-flex gap-3 mb-5">
                    <div class="intent-chip <?= ($detectedIntent==='ticket'?'intent-active':'') ?>" onclick="setIntent('ticket', this)"><i class="bi bi-ticket"></i> CHAMADOS</div>
                    <div class="intent-chip <?= ($detectedIntent==='user'?'intent-active':'') ?>" onclick="setIntent('user', this)"><i class="bi bi-person-plus"></i> USUÁRIOS</div>
                    <div class="intent-chip <?= ($detectedIntent==='asset'?'intent-active':'') ?>" onclick="setIntent('asset', this)"><i class="bi bi-pc-display"></i> ATIVOS</div>
                </div>

                <div class="row g-3 mb-5">
                    <?php foreach ($headers as $h): ?>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-4">
                                <label class="small fw-bold opacity-50 d-block mb-1"><?=$h?></label>
                                <select name="mapping[<?=$h?>]" class="form-select border-0 bg-transparent fw-bold small">
                                    <option value="">(Ignorar)</option>
                                    <optgroup label="Tickets">
                                        <option value="problema" <?=($mapping[$h]??'')==='problema'?'selected':''?>>Descrição</option>
                                    </optgroup>
                                    <optgroup label="Usuários">
                                        <option value="login" <?=($mapping[$h]??'')==='login'?'selected':''?>>Usuário / Login</option>
                                        <option value="nome" <?=($mapping[$h]??'')==='nome'?'selected':''?>>Nome</option>
                                        <option value="email" <?=($mapping[$h]??'')==='email'?'selected':''?>>E-mail</option>
                                    </optgroup>
                                    <optgroup label="Ativos/Hardware">
                                        <option value="nome" <?=($mapping[$h]??'')==='nome'?'selected':''?>>Nome do Ativo</option>
                                        <option value="serie" <?=($mapping[$h]??'')==='serie'?'selected':''?>>Nº de Série</option>
                                        <option value="patrimonio" <?=($mapping[$h]??'')==='patrimonio'?'selected':''?>>Patrimônio</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn-p w-100 py-4">EXECUTAR CARGA COGNITIVA</button>
            </div>
        </form>

    <?php else: ?>
        <div class="sol-card text-center">
            <h4 class="fw-bold mb-5">Arraste sua planilha para análise profunda</h4>
            <form method="post" action="<?= $self ?>?step=preview" enctype="multipart/form-data">
                <input type="file" name="source_file" class="form-control mb-4" onchange="this.form.submit()">
                <textarea name="paste_data" class="form-control mb-4" style="min-height: 200px;" placeholder="Ou cole os dados aqui..."></textarea>
                <button type="submit" class="btn-p w-100 py-3">ANALISAR CONTEXTO</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    const themeBtn = document.getElementById('theme-btn');
    const themeIcon = document.getElementById('theme-icon');
    function setTheme(t) { document.documentElement.setAttribute('data-theme', t); localStorage.setItem('sol-theme', t); themeIcon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill'; }
    themeBtn?.addEventListener('click', () => { const c = document.documentElement.getAttribute('data-theme'); setTheme(c === 'dark' ? 'light' : 'dark'); });
    setTheme(localStorage.getItem('sol-theme') || 'light');
    function setIntent(v, el) { document.getElementById('intent_field').value = v; document.querySelectorAll('.intent-chip').forEach(c => c.classList.remove('intent-active')); el.classList.add('intent-active'); }
</script>
<?php Html::footer(); ?>
