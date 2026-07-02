<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';
Session::checkLoginUser();

require_once __DIR__ . '/../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use SOLPI\Companies\Entities\Company;
use SOLPI\Companies\Repositories\CompanyRepository;
use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;
use SOLPI\Users\Entities\User;
use SOLPI\Users\Repositories\UserRepository;

$parser = new ExcelParser();
$detector = new ColumnDetector();
$step = $_GET['step'] ?? 'upload';
$uploadDir = sys_get_temp_dir() . '/solpi_import/';
is_dir($uploadDir) || mkdir($uploadDir, 0777, true);

function solpi_import_normalize_text(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    return trim($text);
}

function solpi_import_html_table_to_tsv(string $html): string
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

function solpi_import_detect_delimiter(string $line): string
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

function solpi_import_parse_delimited_text(string $text): array
{
    $text = solpi_import_normalize_text($text);
    if ($text === '') {
        return [];
    }

    if (stripos($text, '<table') !== false && stripos($text, '</table>') !== false) {
        $text = solpi_import_html_table_to_tsv($text);
    }

    $lines = preg_split('/\n+/', $text) ?: [];
    $lines = array_values(array_filter($lines, static fn(string $line): bool => trim($line) !== ''));
    if ($lines === []) {
        return [];
    }

    $delimiter = solpi_import_detect_delimiter($lines[0]);
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

function solpi_import_parse_source_file(string $tmpFile, string $originalName, ExcelParser $parser): array
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (in_array($extension, ['csv', 'tsv', 'txt'], true)) {
        $content = file_get_contents($tmpFile);
        return solpi_import_parse_delimited_text($content === false ? '' : $content);
    }

    return $parser->parse($tmpFile);
}

function solpi_import_store_text_payload(string $text, string $uploadDir): array
{
    $normalized = solpi_import_normalize_text($text);
    if ($normalized === '') {
        return ['', ''];
    }

    $isHtmlTable = stripos($normalized, '<table') !== false && stripos($normalized, '</table>') !== false;
    $content = $isHtmlTable ? solpi_import_html_table_to_tsv($normalized) : $normalized;
    $suffix = str_contains($content, "\t") ? 'tsv' : 'csv';
    $tmpFile = $uploadDir . uniqid('import_', true) . '.' . $suffix;
    file_put_contents($tmpFile, $content);

    return [$tmpFile, 'clipboard.' . $suffix];
}

function solpi_import_field_value(array $row, array $fieldMap, string $field): string
{
    $header = $fieldMap[$field] ?? '';
    if ($header === '' || !array_key_exists($header, $row)) {
        return '';
    }

    return trim((string) $row[$header]);
}

function solpi_import_reverse_mapping(array $mapping): array
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

function solpi_import_resolve_company_id(string $companyName): array
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
    $company->setSetting('source', 'solpi-import');

    return ['id' => $repository->create($company), 'created' => true];
}

function solpi_import_user_from_row(array $row): User
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

function solpi_import_resolve_user_id(string $name, ?string $email, ?string $phone, ?string $department, ?string $position, ?int $companyId): array
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

        $repository->update((int) $existing['id'], solpi_import_user_from_row($merged));

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
    $user->setSetting('source', 'solpi-import');

    return ['id' => $repository->create($user), 'created' => true];
}

Html::header('Janela de Importação SOLPI', '', 'central');

if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmpFile = (string) ($_POST['tmp_file'] ?? '');
    $sourceName = (string) ($_POST['source_name'] ?? '');
    $mapping = is_array($_POST['mapping'] ?? null) ? $_POST['mapping'] : [];
    $fieldMap = solpi_import_reverse_mapping($mapping);
    $created = 0;
    $companyCreated = 0;
    $errors = [];

    if ($tmpFile !== '' && file_exists($tmpFile)) {
        $rows = solpi_import_parse_source_file($tmpFile, $sourceName, $parser);

        foreach ($rows as $index => $row) {
            try {
                $empresa = solpi_import_field_value($row, $fieldMap, 'empresa');
                $nome = solpi_import_field_value($row, $fieldMap, 'nome');
                $problema = solpi_import_field_value($row, $fieldMap, 'problema');
                $telefone = solpi_import_field_value($row, $fieldMap, 'telefone');
                $email = solpi_import_field_value($row, $fieldMap, 'email');
                $department = solpi_import_field_value($row, $fieldMap, 'department');
                $position = solpi_import_field_value($row, $fieldMap, 'position');
                $categoria = solpi_import_field_value($row, $fieldMap, 'categoria');
                $prioridade = solpi_import_field_value($row, $fieldMap, 'prioridade');
                $local = solpi_import_field_value($row, $fieldMap, 'local');
                $tecnico = solpi_import_field_value($row, $fieldMap, 'tecnico');
                $statusInformado = solpi_import_field_value($row, $fieldMap, 'status');

                if ($problema === '') {
                    continue;
                }

                $companyId = null;
                if ($empresa !== '') {
                    $companyResult = solpi_import_resolve_company_id($empresa);
                    $companyId = $companyResult['id'];
                    if (!empty($companyResult['created'])) {
                        $companyCreated++;
                    }
                }

                $userId = null;
                if ($nome !== '' || $email !== '') {
                    $userResult = solpi_import_resolve_user_id($nome, $email, $telefone, $department, $position, $companyId);
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
                if ($prioridade !== '') {
                    $content .= "\nPrioridade informada: {$prioridade}";
                }
                if ($local !== '') {
                    $content .= "\nLocal: {$local}";
                }
                if ($tecnico !== '') {
                    $content .= "\nTecnico: {$tecnico}";
                }
                if ($statusInformado !== '') {
                    $content .= "\nStatus informado: {$statusInformado}";
                }

                global $DB;
                $now = date('Y-m-d H:i:s');

                $DB->insert('glpi_tickets', [
                    'entities_id'          => 0,
                    'name'                 => $DB->escape($title),
                    'content'              => $DB->escape('<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>'),
                    'date'                 => $now,
                    'date_creation'        => $now,
                    'date_mod'             => $now,
                    'status'               => 1,
                    'type'                 => 1,
                    'priority'             => 3,
                    'urgency'              => 3,
                    'impact'               => 3,
                    'requesttypes_id'      => 1,
                    'users_id_lastupdater' => 0,
                    'is_deleted'           => 0,
                ]);

                $glpiId = (int) $DB->insertId();

                $DB->insert('glpi_plugin_solpi_tickets', [
                    'glpi_ticket_id' => $glpiId,
                    'company_id'     => $companyId,
                    'user_id'        => $userId,
                    'title'          => $title,
                    'description'    => $content,
                    'status'         => 'OPEN',
                    'priority'       => $prioridade !== '' ? $prioridade : 'normal',
                    'category'       => $categoria !== '' ? $categoria : null,
                    'metadata'       => json_encode([
                        'source_name' => $sourceName,
                        'row_index' => $index + 2,
                        'raw_row' => $row,
                        'department' => $department,
                        'position' => $position,
                    ], JSON_UNESCAPED_UNICODE),
                    'opened_at'      => $now,
                ]);

                $created++;
            } catch (Throwable $e) {
                $errors[] = 'Linha ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        @unlink($tmpFile);
    }

    echo '<div class="container-fluid py-3">';
    echo '<h3>Importação Concluída</h3>';
    echo '<div class="alert alert-success"><strong>' . $created . ' chamados criados com sucesso!</strong></div>';
    echo '<div class="alert alert-info">' . $companyCreated . ' empresa(s) cadastrada(s) ou reaproveitada(s).</div>';

    if ($errors !== []) {
        echo '<div class="alert alert-warning"><strong>Erros:</strong><ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
        }
        echo '</ul></div>';
    }

    echo '<a href="import.php" class="btn btn-primary">Nova Importação</a>';
    echo ' <a href="../../../front/ticket.php" class="btn btn-secondary">Ver Chamados no GLPI</a>';
    echo '</div>';

    Html::footer();
    exit;
}

$rows = [];
$headers = [];
$mapping = [];
$preview = [];
$message = '';
$msgType = 'info';
$tmpFile = '';
$sourceName = '';

if ($step === 'preview') {
    $pasteData = trim((string) ($_POST['paste_data'] ?? ''));
    $uploadedFile = $_FILES['source_file'] ?? null;

    if ($pasteData !== '') {
        [$tmpFile, $sourceName] = solpi_import_store_text_payload($pasteData, $uploadDir);
        if ($tmpFile !== '') {
            $rows = solpi_import_parse_source_file($tmpFile, $sourceName, $parser);
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
            $rows = solpi_import_parse_source_file($tmpFile, $sourceName, $parser);
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
<?php if ($step === 'preview' && $rows !== []): ?>
<div class="container-fluid py-3">
    <h3>Mapeamento de Colunas — <?= htmlspecialchars($sourceName !== '' ? $sourceName : 'conteúdo analisado', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
    <p class="text-muted"><?= count($rows) ?> linhas detectadas. Ajuste o mapeamento e clique em importar.</p>

    <form method="post" action="import.php?step=import">
        <input type="hidden" name="tmp_file" value="<?= htmlspecialchars($tmpFile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="source_name" value="<?= htmlspecialchars($sourceName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

        <div class="card mb-4">
            <div class="card-header fw-bold">Mapeamento de Colunas</div>
            <div class="card-body">
                <table class="table table-sm table-bordered" style="max-width: 720px">
                    <tr><th>Coluna de origem</th><th>Campo SOLPI</th></tr>
                    <?php foreach ($headers as $header): ?>
                        <tr>
                            <td><?= htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td>
                                <select name="mapping[<?= htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>]" class="form-select form-select-sm">
                                    <option value="">— ignorar —</option>
                                    <?php foreach (['empresa','nome','telefone','email','department','position','problema','prioridade','categoria','serie','local','tecnico','status'] as $field): ?>
                                        <option value="<?= htmlspecialchars($field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= ($mapping[$header] ?? '') === $field ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-bold">Preview (primeiras <?= count($preview) ?> linhas)</div>
            <div class="card-body" style="overflow-x:auto">
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th><?= htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview as $row): ?>
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                    <td><?= htmlspecialchars(mb_strimwidth((string) ($row[$header] ?? ''), 0, 50, '...'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg">✅ Importar <?= count($rows) ?> chamados para o GLPI</button>
        <a href="import.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
<?php elseif ($step === 'preview'): ?>
<div class="container-fluid py-3">
    <div class="alert alert-danger"><?= htmlspecialchars($message !== '' ? $message : 'Não foi possível analisar o conteúdo enviado.', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <a href="import.php" class="btn btn-primary">Voltar</a>
</div>
<?php else: ?>
<style>
    .solpi-import-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .solpi-hero {
        border: 1px solid rgba(13, 110, 253, 0.15);
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(32, 201, 151, 0.08));
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }

    .solpi-dropzone {
        border: 2px dashed #4f8df5;
        border-radius: 1rem;
        background: #f8fbff;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem;
        transition: all .15s ease;
        cursor: pointer;
    }

    .solpi-dropzone.dragover {
        border-color: #0d6efd;
        background: #eef5ff;
        transform: scale(1.01);
    }

    .solpi-paste {
        min-height: 250px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        resize: vertical;
    }

    .solpi-helper {
        font-size: .92rem;
        color: #6c757d;
    }
</style>

<div class="container-fluid py-3 solpi-import-shell">
    <div class="solpi-hero">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
            <div class="flex-grow-1">
                <h3 class="mb-1">📥 Janela de Importação SOLPI</h3>
                <p class="mb-0 text-muted">Arraste um arquivo, cole uma tabela copiada de outro site/planilha, ou envie Excel/CSV. O SOLPI analisa as colunas e gera os chamados com a empresa vinculada.</p>
            </div>
            <div class="text-lg-end">
                <a href="dashboard.php" class="btn btn-outline-primary me-2">Abrir dashboard</a>
                <a href="tickets.php" class="btn btn-outline-secondary">Ver tickets</a>
            </div>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($msgType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold">1. Arraste e solte o arquivo</div>
                <div class="card-body">
                    <form method="post" action="import.php?step=preview" enctype="multipart/form-data" id="solpi-import-form">
                        <div id="solpi-dropzone" class="solpi-dropzone mb-3" tabindex="0" role="button" aria-label="Arraste e solte o arquivo aqui">
                            <div>
                                <div class="fw-bold mb-2">Solte aqui a planilha ou arquivo CSV</div>
                                <div class="solpi-helper">Suporta .xlsx, .xls, .csv, .tsv e .txt.</div>
                                <div class="mt-3 text-primary fw-semibold" id="source_note">Clique ou arraste um arquivo para esta área</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="source_file">Arquivo</label>
                            <input type="file" name="source_file" id="source_file" class="form-control" accept=".xlsx,.xls,.csv,.tsv,.txt">
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary btn-lg">Analisar arquivo</button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg" id="clear-file-btn">Limpar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold">2. Cole dados de planilha ou de outro site</div>
                <div class="card-body">
                    <form method="post" action="import.php?step=preview" id="solpi-paste-form">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="paste_data">Colar tabela / texto tabular</label>
                            <textarea name="paste_data" id="paste_data" class="form-control solpi-paste" placeholder="Cole aqui a tabela copiada de uma planilha ou site. Se vier como tabela HTML, o SOLPI converte para colunas automaticamente."></textarea>
                            <div class="solpi-helper mt-2">Copie a tabela inteira no site/planilha e cole aqui. O SOLPI entende tabulações, CSV e tabela HTML simples.</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-success btn-lg">Analisar dados colados</button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" id="fill-example-btn">Exemplo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header fw-bold">Colunas reconhecidas automaticamente</div>
        <div class="card-body">
            <div class="row">
                <?php
                $examples = [
                    'empresa' => 'Empresa, Company, Cliente, CNPJ',
                    'nome' => 'Nome, Solicitante, Contato, Usuário',
                    'telefone' => 'Telefone, Celular, WhatsApp',
                    'email' => 'Email, E-mail, Mail',
                    'department' => 'Departamento, Setor, Área',
                    'position' => 'Cargo, Função, Posição',
                    'problema' => 'Problema, Descrição, Chamado, Assunto, Título',
                    'prioridade' => 'Prioridade, Urgência, SLA',
                    'categoria' => 'Categoria, Tipo, Setor',
                    'local' => 'Local, Unidade, Filial',
                    'tecnico' => 'Técnico, Responsável, Analista',
                    'status' => 'Status, Situação, Estado',
                ];

                foreach ($examples as $field => $cols):
                ?>
                <div class="col-md-6 col-xl-4 mb-2">
                    <small><strong><?= htmlspecialchars($field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>: <span class="text-muted"><?= htmlspecialchars($cols, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fileInput = document.getElementById('source_file');
    const dropzone = document.getElementById('solpi-dropzone');
    const sourceNote = document.getElementById('source_note');
    const pasteArea = document.getElementById('paste_data');
    const clearButton = document.getElementById('clear-file-btn');
    const fillExampleButton = document.getElementById('fill-example-btn');

    const htmlTableToTsv = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const rows = [...doc.querySelectorAll('tr')].map((tr) => {
            const cells = [...tr.querySelectorAll('th,td')].map((cell) => cell.textContent.replace(/\s+/g, ' ').trim());
            return cells.join('\t');
        }).filter(Boolean);

        return rows.join('\n');
    };

    const setFileLabel = (label) => {
        sourceNote.textContent = label || 'Arquivo selecionado';
    };

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
            setFileLabel(event.dataTransfer.files[0].name);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files.length > 0) {
            setFileLabel(fileInput.files[0].name);
        }
    });

    pasteArea.addEventListener('paste', (event) => {
        const html = event.clipboardData ? event.clipboardData.getData('text/html') : '';
        if (html && html.toLowerCase().includes('<table')) {
            event.preventDefault();
            pasteArea.value = htmlTableToTsv(html);
        }
    });

    clearButton.addEventListener('click', () => {
        fileInput.value = '';
        pasteArea.value = '';
        setFileLabel('Clique ou arraste um arquivo para esta área');
    });

    fillExampleButton.addEventListener('click', () => {
        pasteArea.value = [
            'Empresa\tNome\tTelefone\tEmail\tProblema\tPrioridade\tCategoria',
            'ACME LTDA\tMaria Silva\t(11) 99999-0000\tmaria@acme.com\tSem acesso ao sistema\tAlta\tSuporte',
            'Beta Serviços\tJoão Santos\t(11) 98888-1111\tjoao@beta.com\tImpressora offline\tMédia\tInfra'
        ].join('\n');
        pasteArea.focus();
    });
})();
</script>
<?php
endif;

Html::footer();