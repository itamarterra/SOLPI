<?php

declare(strict_types=1);

require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use SOLPI\Companies\Entities\Company;
use SOLPI\Companies\Repositories\CompanyRepository;
use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;
use SOLPI\Users\Entities\User;
use SOLPI\Users\Repositories\UserRepository;

global $DB;
$DB = new DB();

$uploadDir = sys_get_temp_dir() . '/solpi_import/';
is_dir($uploadDir) || mkdir($uploadDir, 0777, true);

function solpi_import_public_normalize_text(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    return trim($text);
}

function solpi_import_public_html_table_to_tsv(string $html): string
{
    if (!class_exists(DOMDocument::class)) {
        return $html;
    }

    $document = new DOMDocument();
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $lines = [];
    foreach ($document->getElementsByTagName('tr') as $rowNode) {
        $cells = [];
        foreach ($rowNode->childNodes as $cellNode) {
            if (!in_array($cellNode->nodeName, ['td', 'th'], true)) {
                continue;
            }

            $cells[] = trim(preg_replace('/\s+/', ' ', $cellNode->textContent) ?? '');
        }

        if ($cells !== []) {
            $lines[] = implode("\t", $cells);
        }
    }

    return implode("\n", $lines);
}

function solpi_import_public_detect_delimiter(string $line): string
{
    $candidates = ["\t", ';', ',', '|'];
    $best = "\t";
    $bestCount = -1;

    foreach ($candidates as $candidate) {
        $count = substr_count($line, $candidate);
        if ($count > $bestCount) {
            $best = $candidate;
            $bestCount = $count;
        }
    }

    return $best;
}

function solpi_import_public_parse_delimited_text(string $text): array
{
    $text = solpi_import_public_normalize_text($text);
    if ($text === '') {
        return [];
    }

    if (stripos($text, '<table') !== false && stripos($text, '</table>') !== false) {
        $text = solpi_import_public_html_table_to_tsv($text);
    }

    $lines = preg_split('/\n+/', $text) ?: [];
    $lines = array_values(array_filter($lines, static fn(string $line): bool => trim($line) !== ''));
    if ($lines === []) {
        return [];
    }

    $delimiter = solpi_import_public_detect_delimiter($lines[0]);
    $headers = array_map(static fn(string $value): string => trim($value), str_getcsv((string) array_shift($lines), $delimiter));
    $rows = [];

    foreach ($lines as $line) {
        $values = str_getcsv($line, $delimiter);
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $row[$header] = trim((string) ($values[$index] ?? ''));
        }

        if ($row !== [] && array_filter($row, static fn(mixed $value): bool => trim((string) $value) !== '') !== []) {
            $rows[] = $row;
        }
    }

    return $rows;
}

function solpi_import_public_parse_source_file(string $tmpFile, string $originalName, ExcelParser $parser): array
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (in_array($extension, ['csv', 'tsv', 'txt'], true)) {
        $content = file_get_contents($tmpFile);
        return solpi_import_public_parse_delimited_text($content === false ? '' : $content);
    }

    return $parser->parse($tmpFile);
}

function solpi_import_public_store_text_payload(string $text, string $uploadDir): array
{
    $normalized = solpi_import_public_normalize_text($text);
    if ($normalized === '') {
        return ['', ''];
    }

    $isHtmlTable = stripos($normalized, '<table') !== false && stripos($normalized, '</table>') !== false;
    $content = $isHtmlTable ? solpi_import_public_html_table_to_tsv($normalized) : $normalized;
    $suffix = str_contains($content, "\t") ? 'tsv' : 'csv';
    $tmpFile = $uploadDir . uniqid('import_', true) . '.' . $suffix;
    file_put_contents($tmpFile, $content);

    return [$tmpFile, 'clipboard.' . $suffix];
}

function solpi_import_public_field_value(array $row, array $fieldMap, string $field): string
{
    $header = $fieldMap[$field] ?? '';
    if ($header === '' || !array_key_exists($header, $row)) {
        return '';
    }

    return trim((string) $row[$header]);
}

function solpi_import_public_reverse_mapping(array $mapping): array
{
    $fieldMap = [];
    foreach ($mapping as $header => $field) {
        $header = trim((string) $header);
        $field = trim((string) $field);

        if ($header !== '' && $field !== '') {
            $fieldMap[$field] = $header;
        }
    }

    return $fieldMap;
}

function solpi_import_public_resolve_company_id(string $companyName): array
{
    $companyName = trim($companyName);
    if ($companyName === '') {
        return ['id' => null, 'created' => false];
    }

    $repository = new CompanyRepository();
    $existing = $repository->findBy(['name' => $companyName]);
    if (is_array($existing) && isset($existing['id'])) {
        return ['id' => (int) $existing['id'], 'created' => false];
    }

    $company = new Company(Uuid::uuid4()->toString(), $companyName);
    $company->setSetting('source', 'solpi-import-public');

    return ['id' => $repository->create($company), 'created' => true];
}

  function solpi_import_public_user_from_row(array $row): User
  {
    $uuid = trim((string) ($row['uuid'] ?? ''));
    if ($uuid === '') {
      $uuid = Uuid::uuid4()->toString();
    }

    $user = new User(
      $uuid,
      trim((string) ($row['name'] ?? ''))
    );

    if (isset($row['id'])) {
      $user->setId((int) $row['id']);
    }

    if (!empty($row['email'])) {
      $user->setEmail((string) $row['email']);
    }
    if (!empty($row['phone'])) {
      $user->setPhone((string) $row['phone']);
    }
    if (!empty($row['department'])) {
      $user->setDepartment((string) $row['department']);
    }
    if (!empty($row['position'])) {
      $user->setPosition((string) $row['position']);
    }
    if (!empty($row['company_id'])) {
      $user->setCompanyId((int) $row['company_id']);
    }

    return $user;
  }

  function solpi_import_public_resolve_user_id(string $name, ?string $email, ?string $phone, ?string $department, ?string $position, ?int $companyId): array
  {
    $name = trim($name);
    $email = trim((string) $email);
    $phone = trim((string) $phone);
      $department = trim((string) $department);
      $position = trim((string) $position);

    if ($name === '' && $email === '' && $phone === '') {
      return ['id' => null, 'created' => false];
    }

    $userName = $name !== '' ? $name : ($email !== '' ? $email : 'Contato ' . $phone);

    $repository = new UserRepository();
    $existing = null;

    if ($email !== '') {
      $existing = $repository->findByEmail($email);
    }

    if ($existing === null && $phone !== '') {
      $existing = $repository->findByPhone($phone);
    }

    if ($existing === null && $name !== '') {
      $existing = $repository->findByName($name);
    }

    if (is_array($existing) && isset($existing['id'])) {
      $merged = $existing;
      $merged['name'] = $name !== '' ? $name : (string) ($existing['name'] ?? $userName ?? '');

      if ($email !== '') {
        $merged['email'] = $email;
      }
      if ($phone !== '') {
        $merged['phone'] = $phone;
      }
      if ($department !== '') {
        $merged['department'] = $department;
      }
      if ($position !== '') {
        $merged['position'] = $position;
      }
      if ($companyId !== null) {
        $merged['company_id'] = $companyId;
      }

      $repository->update((int) $existing['id'], solpi_import_public_user_from_row($merged));

      return ['id' => (int) $existing['id'], 'created' => false];
    }

    $user = new User(Uuid::uuid4()->toString(), $userName);

    if ($email !== '') {
      $user->setEmail($email);
    }
    if ($phone !== '') {
      $user->setPhone($phone);
    }
    if ($department !== '') {
      $user->setDepartment($department);
    }
    if ($position !== '') {
      $user->setPosition($position);
    }
    if ($companyId !== null) {
      $user->setCompanyId($companyId);
    }
    $user->setSetting('source', 'solpi-import-public');

    return ['id' => $repository->create($user), 'created' => true];
  }

$step = $_GET['step'] ?? 'upload';
$rows = [];
$headers = [];
$mapping = [];
$preview = [];
$message = '';
$msgType = 'info';
$tmpFile = '';
$sourceName = '';

if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmpFile = (string) ($_POST['tmp_file'] ?? '');
    $sourceName = (string) ($_POST['source_name'] ?? '');
    $mapping = is_array($_POST['mapping'] ?? null) ? $_POST['mapping'] : [];
    $fieldMap = solpi_import_public_reverse_mapping($mapping);
    $created = 0;
    $companyCreated = 0;
    $errors = [];

    if ($tmpFile !== '' && file_exists($tmpFile)) {
        $parser = new ExcelParser();
        $rows = solpi_import_public_parse_source_file($tmpFile, $sourceName, $parser);

        foreach ($rows as $index => $row) {
            try {
                $empresa = solpi_import_public_field_value($row, $fieldMap, 'empresa');
                $nome = solpi_import_public_field_value($row, $fieldMap, 'nome');
                $problema = solpi_import_public_field_value($row, $fieldMap, 'problema');
                $telefone = solpi_import_public_field_value($row, $fieldMap, 'telefone');
                $email = solpi_import_public_field_value($row, $fieldMap, 'email');
                $department = solpi_import_public_field_value($row, $fieldMap, 'department');
                $position = solpi_import_public_field_value($row, $fieldMap, 'position');
                $categoria = solpi_import_public_field_value($row, $fieldMap, 'categoria');
                $prioridade = solpi_import_public_field_value($row, $fieldMap, 'prioridade');

                if ($problema === '') {
                    continue;
                }

                $companyId = null;
                if ($empresa !== '') {
                    $companyResult = solpi_import_public_resolve_company_id($empresa);
                    $companyId = $companyResult['id'];
                    if (!empty($companyResult['created'])) {
                        $companyCreated++;
                    }
                }

                $userId = null;
                if ($nome !== '' || $email !== '') {
                  $userResult = solpi_import_public_resolve_user_id($nome, $email, $telefone, $department, $position, $companyId);
                  $userId = $userResult['id'];
                }

                $title = ($nome !== '' ? $nome . ' - ' : '') . mb_strimwidth($problema, 0, 100, '...');
                $content = $problema;
                if ($empresa !== '') {
                    $content .= "\n\nEmpresa: {$empresa}";
                }
                if ($nome !== '') {
                    $content .= "\nSolicitante: {$nome}";
                }
                if ($telefone !== '') {
                    $content .= "\nTelefone: {$telefone}";
                }
                if ($email !== '') {
                    $content .= "\nEmail: {$email}";
                }
                if ($department !== '') {
                  $content .= "\nDepartamento: {$department}";
                }
                if ($position !== '') {
                  $content .= "\nCargo: {$position}";
                }
                if ($categoria !== '') {
                    $content .= "\nCategoria: {$categoria}";
                }

                $now = date('Y-m-d H:i:s');
                $DB->insert('glpi_tickets', [
                    'entities_id' => 0,
                    'name' => $DB->escape($title),
                    'content' => $DB->escape('<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>'),
                    'date' => $now,
                    'date_creation' => $now,
                    'date_mod' => $now,
                    'status' => 1,
                    'type' => 1,
                    'priority' => 3,
                    'urgency' => 3,
                    'impact' => 3,
                    'requesttypes_id' => 1,
                    'users_id_lastupdater' => 0,
                    'is_deleted' => 0,
                ]);

                $glpiId = (int) $DB->insertId();
                $DB->insert('glpi_plugin_solpi_tickets', [
                    'glpi_ticket_id' => $glpiId,
                    'company_id' => $companyId,
                  'user_id' => $userId,
                    'title' => $title,
                    'description' => $content,
                    'status' => 'OPEN',
                    'priority' => $prioridade !== '' ? $prioridade : 'normal',
                    'category' => $categoria !== '' ? $categoria : null,
                    'metadata' => json_encode([
                        'source_name' => $sourceName,
                        'row_index' => $index + 2,
                        'raw_row' => $row,
                      'department' => $department,
                      'position' => $position,
                    ], JSON_UNESCAPED_UNICODE),
                    'opened_at' => $now,
                ]);

                $created++;
            } catch (Throwable $e) {
                $errors[] = 'Linha ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        @unlink($tmpFile);
    }

    $message = $created . ' chamados criados com sucesso!';
    $msgType = 'success';
    if ($companyCreated > 0) {
        $message .= ' ' . $companyCreated . ' empresa(s) cadastrada(s) ou reaproveitada(s).';
    }
    if ($errors !== []) {
        $message .= ' Erros: ' . implode('; ', $errors);
    }
    $step = 'done';
}

if ($step === 'preview') {
    $pasteData = trim((string) ($_POST['paste_data'] ?? ''));
    $uploadedFile = $_FILES['source_file'] ?? null;
    $parser = new ExcelParser();
    $detector = new ColumnDetector();

    if ($pasteData !== '') {
        [$tmpFile, $sourceName] = solpi_import_public_store_text_payload($pasteData, $uploadDir);
        if ($tmpFile !== '') {
            $rows = solpi_import_public_parse_source_file($tmpFile, $sourceName, $parser);
        }
    } elseif (is_array($uploadedFile) && (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)) {
        $sourceName = (string) ($uploadedFile['name'] ?? 'import.xlsx');
        $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
        $extension = $extension !== '' ? $extension : 'xlsx';
        $tmpFile = $uploadDir . uniqid('import_', true) . '.' . $extension;

        if (!move_uploaded_file((string) $uploadedFile['tmp_name'], $tmpFile)) {
            $message = 'Não foi possível salvar o arquivo enviado.';
            $msgType = 'danger';
        } else {
            $rows = solpi_import_public_parse_source_file($tmpFile, $sourceName, $parser);
        }
    } else {
        $message = 'Selecione um arquivo ou cole os dados da planilha/site para analisar.';
        $msgType = 'warning';
    }

    if ($rows !== []) {
        $headers = array_keys($rows[0]);
        $mapping = $detector->detect($headers);
        $preview = array_slice($rows, 0, 5);
        $message = sprintf('Foram detectadas %d linha(s) para análise.', count($rows));
        $msgType = 'success';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Janela de Importação SOLPI</title>
<link rel="stylesheet" href="/lib/base.min.css">
<style>
body{padding:20px;background:#f4f6f9}
.card{border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.dropzone{border:2px dashed #4f8df5;border-radius:1rem;background:#f8fbff;min-height:220px;display:flex;align-items:center;justify-content:center;text-align:center;padding:1.5rem;cursor:pointer}
.dropzone.dragover{border-color:#0d6efd;background:#eef5ff}
</style>
</head>
<body>
<div class="container-fluid" style="max-width:1180px;margin:auto">
  <div class="d-flex align-items-center mb-4 gap-2">
    <h3 class="mb-0">📥 Janela de Importação SOLPI</h3>
    <a href="/front/ticket.php" class="btn btn-sm btn-outline-secondary ms-auto">Ver Chamados no GLPI</a>
  </div>

<?php if ($message): ?>
  <div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></div>
<?php endif; ?>

<?php if ($step === 'done'): ?>
  <div class="card p-4 text-center">
    <h4>✅ Importação concluída!</h4>
    <a href="/solpi-import.php" class="btn btn-primary mt-3">Nova Importação</a>
    <a href="/front/ticket.php" class="btn btn-success mt-3 ms-2">Ver Chamados no GLPI</a>
  </div>

<?php elseif ($step === 'preview' && $rows): ?>
  <form method="post" action="/solpi-import.php?step=import">
    <input type="hidden" name="tmp_file" value="<?=htmlspecialchars($tmpFile ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>">
    <input type="hidden" name="source_name" value="<?=htmlspecialchars($sourceName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>">

    <div class="card mb-4 p-3">
      <h5>🗂 Mapeamento de Colunas <small class="text-muted">(<?=count($rows)?> linhas)</small></h5>
      <table class="table table-sm table-bordered mt-2" style="max-width:760px">
        <tr><th>Coluna de origem</th><th>Campo SOLPI</th></tr>
        <?php foreach ($headers as $col): ?>
        <tr>
          <td><?=htmlspecialchars($col, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td>
          <td>
            <select name="mapping[<?=htmlspecialchars($col, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>]" class="form-select form-select-sm">
              <option value="">— ignorar —</option>
              <?php foreach (['empresa','nome','telefone','email','department','position','problema','prioridade','categoria','local','tecnico','status'] as $f): ?>
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
        <thead><tr><?php foreach ($headers as $h): ?><th><?=htmlspecialchars($h, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($preview as $r): ?>
            <tr><?php foreach ($headers as $h): ?><td><?=htmlspecialchars(mb_strimwidth((string)($r[$h] ?? ''),0,40,'...'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <button type="submit" class="btn btn-success btn-lg">✅ Importar <?=count($rows)?> chamados</button>
    <a href="/solpi-import.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>

<?php else: ?>
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card p-4 h-100">
        <form method="post" action="/solpi-import.php?step=preview" enctype="multipart/form-data" id="solpi-public-file-form">
          <h5>Arraste e solte o arquivo</h5>
          <div class="dropzone mb-3" id="solpi-public-dropzone">
            <div>
              <div class="fw-bold mb-2">Solte aqui a planilha ou arquivo CSV</div>
              <div class="text-muted">Suporta .xlsx, .xls, .csv, .tsv e .txt.</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Arquivo</label>
            <input type="file" name="source_file" id="solpi-public-file" class="form-control" accept=".xlsx,.xls,.csv,.tsv,.txt">
          </div>
          <button type="submit" class="btn btn-primary btn-lg">Analisar arquivo</button>
        </form>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card p-4 h-100">
        <form method="post" action="/solpi-import.php?step=preview" id="solpi-public-paste-form">
          <h5>Cole dados de planilha ou site</h5>
          <div class="mb-3">
            <textarea name="paste_data" class="form-control" style="min-height:220px;font-family:ui-monospace,monospace" placeholder="Cole aqui uma tabela, CSV ou texto tabular"></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-lg">Analisar dados colados</button>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

</div>
<script>
(function () {
  const fileInput = document.getElementById('solpi-public-file');
  const dropzone = document.getElementById('solpi-public-dropzone');

  if (dropzone && fileInput) {
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (event) => {
      event.preventDefault();
      dropzone.classList.add('dragover');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', (event) => {
      event.preventDefault();
      dropzone.classList.remove('dragover');

      if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
        fileInput.files = event.dataTransfer.files;
      }
    });
  }
})();
</script>
</body>
</html>