<?php
declare(strict_types=1);

/**
 * SOLPI Import — endpoint publico em /solpi-import.php
 * Bypassa o Symfony routing do GLPI 11.
 */

require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;

global $DB;
$DB = new DB();

$uploadDir = sys_get_temp_dir() . '/solpi_import/';
is_dir($uploadDir) || mkdir($uploadDir, 0777, true);

$step    = $_GET['step'] ?? 'upload';
$message = '';
$msgType = '';

// =====================================
// STEP 3: importar
// =====================================
if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmpFile = $_POST['tmp_file'] ?? '';
    $mapping = $_POST['mapping'] ?? [];
    $created = 0;
    $errors  = [];

    if ($tmpFile && file_exists($tmpFile)) {
        $parser = new ExcelParser();
        $rows   = $parser->parse($tmpFile);

        foreach ($rows as $i => $row) {
            try {
                $findCol = static fn($field) => array_search($field, $mapping, true) ?: '';

                $empresa  = trim($row[$findCol('empresa')]  ?? '');
                $nome     = trim($row[$findCol('nome')]     ?? '');
                $problema = trim($row[$findCol('problema')] ?? '');
                $tel      = trim($row[$findCol('telefone')] ?? '');
                $email    = trim($row[$findCol('email')]    ?? '');

                if (empty($problema)) continue;

                $title   = ($nome ? "{$nome} - " : '') . mb_strimwidth($problema, 0, 100, '...');
                $content = $problema;
                if ($empresa) $content .= "\n\nEmpresa: {$empresa}";
                if ($nome)    $content .= "\nSolicitante: {$nome}";
                if ($tel)     $content .= "\nTelefone: {$tel}";
                if ($email)   $content .= "\nEmail: {$email}";

                $now = date('Y-m-d H:i:s');
                $DB->insert('glpi_tickets', [
                    'entities_id' => 0, 'name' => $DB->escape($title),
                    'content' => $DB->escape('<p>' . nl2br(htmlspecialchars($content)) . '</p>'),
                    'date' => $now, 'date_creation' => $now, 'date_mod' => $now,
                    'status' => 1, 'type' => 1, 'priority' => 3,
                    'urgency' => 3, 'impact' => 3, 'requesttypes_id' => 1,
                    'users_id_lastupdater' => 0, 'is_deleted' => 0,
                ]);
                $glpiId = (int)$DB->insertId();
                $DB->insert('glpi_plugin_solpi_tickets', ['glpi_ticket_id' => $glpiId, 'status' => 'OPEN', 'opened_at' => $now]);
                $created++;
            } catch (Throwable $e) {
                $errors[] = "Linha " . ($i + 2) . ": " . $e->getMessage();
            }
        }
        @unlink($tmpFile);
    }

    $message = "{$created} chamados criados com sucesso!";
    $msgType = 'success';
    if ($errors) $message .= ' Erros: ' . implode('; ', $errors);
    $step = 'done';
}

// =====================================
// STEP 2: preview
// =====================================
$rows = $headers = $mapping = $preview = [];
if ($step === 'preview' && isset($_FILES['excel'])) {
    $tmpFile = $uploadDir . uniqid('import_') . '.xlsx';
    move_uploaded_file($_FILES['excel']['tmp_name'], $tmpFile);
    try {
        $parser   = new ExcelParser();
        $detector = new ColumnDetector();
        $rows     = $parser->parse($tmpFile);
        $headers  = array_keys($rows[0] ?? []);
        $mapping  = $detector->detect($headers);
        $preview  = array_slice($rows, 0, 5);
    } catch (Throwable $e) {
        $message = 'Erro ao ler arquivo: ' . $e->getMessage();
        $msgType = 'danger';
        $step = 'upload';
        $tmpFile = '';
    }
}

$glpiUrl = 'http://localhost:8081';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SOLPI — Importar Excel</title>
<link rel="stylesheet" href="<?=$glpiUrl?>/lib/base.min.css">
<style>body{padding:20px;background:#f4f6f9} .card{border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1)}</style>
</head>
<body>
<div class="container-fluid" style="max-width:900px;margin:auto">

  <div class="d-flex align-items-center mb-4 gap-2">
    <h3 class="mb-0">📊 SOLPI — Importar Excel para GLPI</h3>
    <a href="<?=$glpiUrl?>/front/ticket.php" class="btn btn-sm btn-outline-secondary ms-auto">Ver Chamados no GLPI</a>
  </div>

<?php if ($message): ?>
  <div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<?php if ($step === 'done'): ?>
  <div class="card p-4 text-center">
    <h4>✅ Importação concluída!</h4>
    <a href="/solpi-import.php" class="btn btn-primary mt-3">Nova Importação</a>
    <a href="<?=$glpiUrl?>/front/ticket.php" class="btn btn-success mt-3 ms-2">Ver Chamados no GLPI</a>
  </div>

<?php elseif ($step === 'preview' && $rows): ?>
  <form method="post" action="/solpi-import.php?step=import">
    <input type="hidden" name="tmp_file" value="<?=htmlspecialchars($tmpFile ?? '')?>">

    <div class="card mb-4 p-3">
      <h5>🗂 Mapeamento de Colunas <small class="text-muted">(<?=count($rows)?> linhas)</small></h5>
      <table class="table table-sm table-bordered mt-2" style="max-width:600px">
        <tr><th>Coluna do Excel</th><th>Campo SOLPI</th></tr>
        <?php foreach ($headers as $col): ?>
        <tr>
          <td><?=htmlspecialchars($col)?></td>
          <td>
            <select name="mapping[<?=htmlspecialchars($col)?>]" class="form-select form-select-sm">
              <option value="">— ignorar —</option>
              <?php foreach (['empresa','nome','telefone','email','problema','prioridade','categoria'] as $f): ?>
              <option value="<?=$f?>" <?=($mapping[$col]??'')===$f?'selected':''?>><?=$f?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card mb-4 p-3" style="overflow-x:auto">
      <h5>👁 Preview (5 primeiras linhas)</h5>
      <table class="table table-sm table-striped table-bordered mt-2">
        <thead><tr><?php foreach ($headers as $h): ?><th><?=htmlspecialchars($h)?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($preview as $r): ?><tr><?php foreach ($r as $v): ?><td><?=htmlspecialchars(mb_strimwidth($v,0,40,'...'))?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <button type="submit" class="btn btn-success btn-lg">✅ Importar <?=count($rows)?> chamados</button>
    <a href="/solpi-import.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>

<?php else: ?>
  <div class="card p-4">
    <p class="text-muted">Faça upload de uma planilha <strong>.xlsx</strong>. A primeira linha deve ter os nomes das colunas.</p>
    <form method="post" action="/solpi-import.php?step=preview" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label fw-bold">Arquivo Excel (.xlsx)</label>
        <input type="file" name="excel" class="form-control" accept=".xlsx,.xls" required>
        <small class="text-muted">Colunas reconhecidas automaticamente: Empresa, Nome, Telefone, Email, Problema, Descrição, etc.</small>
      </div>
      <button type="submit" class="btn btn-primary btn-lg">📂 Carregar e Mapear Colunas</button>
    </form>
  </div>
<?php endif; ?>

</div>
</body>
</html>