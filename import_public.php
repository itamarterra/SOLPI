<?php

declare(strict_types=1);

/**
 * SOLPI - Janela de Importação Profissional
 * Versão 7.0 - Deep Cognitive & Intelligent Parsing
 */

$root = dirname(__DIR__);
if (!file_exists($root . '/inc/includes.php')) { $root = '/var/www/glpi'; }
require_once $root . '/vendor/autoload.php';

if (class_exists('Glpi\\Kernel\\Kernel')) {
    $glpi_kernel = new \Glpi\Kernel\Kernel();
    $glpi_kernel->boot();
}
require_once $root . '/inc/includes.php';

$loader = $root . '/plugins/solpi/vendor/autoload.php';
if (file_exists($loader)) { require_once $loader; }

if (Session::getLoginUserID() === false) {
    Html::header("Acesso Negado", $_SERVER['PHP_SELF']);
    echo "<div class='container text-center mt-5'><div class='alert alert-warning'>Sessão não encontrada.</div></div>";
    Html::footer(); exit;
}

use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;
use SOLPI\Knowledge\Services\CognitiveIntentService;

$uploadDir = $root . "/files/_tmp/solpi_import/";
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

function solpi_get_id_by_name($table, $name) {
    global $DB;
    if (!$name) return 0;
    $iterator = $DB->request(['FROM' => $table, 'WHERE' => ['name' => $name], 'LIMIT' => 1]);
    if (count($iterator)) {
        $row = $iterator->current();
        return (int)$row['id'];
    }
    // Cria automaticamente se for Entidade ou Grupo
    $item = new $table();
    return (int)$item->add(['name' => $name, 'entities_id' => 0]);
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
        $count = 0; $errors = [];

        foreach ($rows as $idx => $r) {
            try {
                if ($intent === 'user') {
                    $user = new User();
                    $login = $r[$mapping['login'] ?? ''] ?? '';
                    if (!$login) { $errors[] = "Linha " . ($idx+1) . ": Login não encontrado."; continue; }

                    $input = [
                        'name'      => $login,
                        'firstname' => $r[$mapping['nome'] ?? ''] ?? '',
                        'realname'  => $r[$mapping['sobrenome'] ?? ''] ?? '',
                        'phone'     => $r[$mapping['telefone'] ?? ''] ?? '',
                        'mobile'    => $r[$mapping['celular'] ?? ''] ?? '',
                        'is_active' => 1,
                        '_password' => $r[$mapping['senha'] ?? ''] ?? '123456',
                    ];

                    if ($entity = ($r[$mapping['empresa'] ?? ''] ?? '')) {
                        $input['entities_id'] = solpi_get_id_by_name('Entity', $entity);
                    }

                    $userId = $user->add($input);
                    if ($userId) {
                        $count++;
                        if ($group = ($r[$mapping['department'] ?? ''] ?? '')) {
                            $groupId = solpi_get_id_by_name('Group', $group);
                            (new Group_User())->add(['users_id' => $userId, 'groups_id' => $groupId]);
                        }
                        // E-mail (GLPI 10+ requer tabela separada, add() via input costuma funcionar mas validamos)
                        if ($email = ($r[$mapping['email'] ?? ''] ?? '')) {
                            $userEmail = new UserEmail();
                            $userEmail->add(['users_id' => $userId, 'email' => $email, 'is_default' => 1]);
                        }
                    } else { $errors[] = "Linha " . ($idx+1) . ": Erro interno do GLPI ao salvar."; }

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
            } catch (Throwable $e) { $errors[] = "Linha " . ($idx+1) . ": " . $e->getMessage(); }
        }
        @unlink($targetFile);
        $msg = "Processamento concluído: $count registros importados.";
        if ($errors) { $msg .= " ( " . count($errors) . " falhas registradas )"; $msgType = 'warning'; }
        $step = 'done';
    }
}

// AÇÃO: ANALISAR PREVIA
if ($step === 'preview') {
    $file = $_FILES['source_file'] ?? null;
    if ($file && $file['error'] === 0) {
        $tmpFile = $uploadDir . uniqid('sp_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        move_uploaded_file($file['tmp_name'], $tmpFile);
        $parser = new ExcelParser();
        $rows = $parser->parse($tmpFile);
        if ($rows) {
            $headers = array_keys($rows[0]);
            $detectedIntent = (new CognitiveIntentService())->detect($headers);
            $mapping = (new ColumnDetector())->detect($headers);
        } else { $msg = "Erro: Não foi possível detectar o cabeçalho dos dados. Certifique-se de que a planilha não possui muitas linhas vazias no topo."; $step = "upload"; }
    }
}

Html::header("SOLPI Import", $self);
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root { --sol-p: #4f46e5; --sol-bg: #f8fafc; --sol-card: #ffffff; --sol-border: #e2e8f0; --sol-text: #1e293b; }
    [data-theme="dark"] { --sol-bg: #0f172a; --sol-card: #1e293b; --sol-border: #334155; --sol-text: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: var(--sol-bg) !important; color: var(--sol-text) !important; transition: 0.4s; margin: 0; }
    .sol-wrap { max-width: 1200px; margin: 3rem auto; padding: 0 2rem; }
    .sol-card { background: var(--sol-card); border-radius: 28px; border: 1px solid var(--sol-border); padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    .intent-badge { padding: 12px 24px; border-radius: 15px; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 10px; border: 2px solid transparent; cursor: pointer; transition: 0.3s; background: rgba(100, 116, 139, 0.05); color: #94a3b8; }
    .intent-active { border-color: var(--sol-p); background: rgba(79, 70, 229, 0.1); color: var(--sol-p); }
</style>

<div class="sol-wrap">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold m-0">SOLPI <span class="text-primary">IMPORT v7.0</span></h2>
        <div id="theme-btn" style="cursor:pointer; font-size: 1.5rem;"><i class="bi bi-moon-stars-fill"></i></div>
    </header>

    <?php if ($msg): ?>
        <div class="alert alert-<?=$msgType?> border-0 rounded-4 shadow-sm mb-5 p-4 fw-bold">
            <i class="bi bi-info-circle-fill me-2"></i> <?=$msg?>
        </div>
    <?php endif; ?>

    <?php if ($step === 'done'): ?>
        <div class="sol-card text-center py-5">
            <div class="display-1 text-success mb-4"><i class="bi bi-check2-circle"></i></div>
            <h2 class="fw-bold">Importação Finalizada</h2>
            <div class="mt-4">
                <a href="<?= $self ?>" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow">NOVA IMPORTAÇÃO</a>
            </div>
        </div>

    <?php elseif ($step === 'preview' && $rows): ?>
        <form method="post" action="<?= $self ?>?step=import">
            <input type="hidden" name="tmp_file" value="<?=$tmpFile?>">
            <input type="hidden" name="intent" id="intent_field" value="<?=$detectedIntent?>">

            <div class="sol-card mb-4">
                <h5 class="fw-bold mb-4">O SOLPI analisou os dados e sugere:</h5>
                <div class="d-flex gap-3 mb-5">
                    <div class="intent-badge <?= ($detectedIntent==='ticket'?'intent-active':'') ?>" onclick="setIntent('ticket', this)">
                        <i class="bi bi-ticket-perforated"></i> ABERTURA DE CHAMADOS
                    </div>
                    <div class="intent-badge <?= ($detectedIntent==='user'?'intent-active':'') ?>" onclick="setIntent('user', this)">
                        <i class="bi bi-person-plus"></i> CADASTRO DE USUÁRIOS
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <?php foreach ($headers as $h): ?>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-4 bg-light bg-opacity-10">
                                <label class="small fw-bold opacity-50 d-block mb-1"><?=$h?></label>
                                <select name="mapping[<?=$h?>]" class="form-select form-select-sm border-0 bg-transparent">
                                    <option value="">(Ignorar)</option>
                                    <optgroup label="Dados de Usuário">
                                        <option value="login" <?=($mapping[$h]??'')==='login'?'selected':''?>>Usuário / Login</option>
                                        <option value="nome" <?=($mapping[$h]??'')==='nome'?'selected':''?>>Primeiro Nome</option>
                                        <option value="sobrenome" <?=($mapping[$h]??'')==='sobrenome'?'selected':''?>>Sobrenome</option>
                                        <option value="email" <?=($mapping[$h]??'')==='email'?'selected':''?>>E-mail</option>
                                        <option value="empresa" <?=($mapping[$h]??'')==='empresa'?'selected':''?>>Entidade (Gold Citrus)</option>
                                        <option value="department" <?=($mapping[$h]??'')==='department'?'selected':''?>>Departamento</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-4 rounded-4 border mb-5">
                    <h6 class="fw-bold mb-3">Prévia dos Dados (Detecção Inteligente de Cabeçalho)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm m-0">
                            <thead><tr><?php foreach($headers as $h) echo "<th>$h</th>"; ?></tr></thead>
                            <tbody>
                                <?php foreach(array_slice($rows,0,3) as $r): ?>
                                    <tr><?php foreach($headers as $h) echo "<td>".mb_strimwidth((string)$r[$h],0,30,'...')."</td>"; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-4 fw-bold rounded-4 shadow-lg">INICIAR PROCESSAMENTO PROFUNDO</button>
            </div>
        </form>

    <?php else: ?>
        <div class="sol-card text-center py-5">
            <i class="bi bi-file-earmark-spreadsheet text-primary display-4 mb-4"></i>
            <h4 class="fw-bold mb-5">Selecione a planilha da Gold Citrus</h4>
            <form method="post" action="<?= $self ?>?step=preview" enctype="multipart/form-data">
                <input type="file" name="source_file" class="form-control mb-5 mx-auto" style="max-width: 400px;" onchange="this.form.submit()">
                <p class="text-muted small">Suporte para Excel e CSV com cabeçalhos decorativos.</p>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    function setIntent(val, el) {
        document.getElementById('intent_field').value = val;
        document.querySelectorAll('.intent-badge').forEach(b => b.classList.remove('intent-active'));
        el.classList.add('intent-active');
    }
</script>
<?php Html::footer(); ?>
