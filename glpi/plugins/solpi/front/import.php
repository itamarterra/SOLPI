<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';
Session::checkLoginUser();

// Carrega apenas o mapeamento PSR-4 do SOLPI (sem Bootstrap::initialize)
require_once __DIR__ . '/../vendor/autoload.php';

use SOLPI\Knowledge\Parsers\ExcelParser;
use SOLPI\Knowledge\Services\ColumnDetector;

Html::header('Importar Excel - SOLPI', '', 'central');

$parser   = new ExcelParser();
$detector = new ColumnDetector();

$step      = $_GET['step'] ?? 'upload';
$uploadDir = sys_get_temp_dir() . '/solpi_import/';
is_dir($uploadDir) || mkdir($uploadDir, 0777, true);

// =============================================
// STEP 3: processar importacao
// =============================================
if ($step === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmpFile  = $_POST['tmp_file'] ?? '';
    $mapping  = $_POST['mapping'] ?? [];
    $created  = 0;
    $errors   = [];

    if ($tmpFile && file_exists($tmpFile)) {
        $rows = $parser->parse($tmpFile);

        foreach ($rows as $i => $row) {
            try {
                $empresa  = trim($row[$mapping['empresa'] ?? ''] ?? '');
                $nome     = trim($row[$mapping['nome'] ?? ''] ?? '');
                $problema = trim($row[$mapping['problema'] ?? ''] ?? '');
                $tel      = trim($row[$mapping['telefone'] ?? ''] ?? '');
                $email    = trim($row[$mapping['email'] ?? ''] ?? '');
                $cat      = trim($row[$mapping['categoria'] ?? ''] ?? '');

                if (empty($problema)) {
                    continue; // pula linha sem descrição
                }

                $title   = ($nome   ? "{$nome} - " : '') . mb_strimwidth($problema, 0, 100, '...');
                $content = $problema;
                if ($empresa)  $content .= "\n\nEmpresa: {$empresa}";
                if ($nome)     $content .= "\nSolicitante: {$nome}";
                if ($tel)      $content .= "\nTelefone: {$tel}";
                if ($email)    $content .= "\nEmail: {$email}";

                global $DB;
                $DB->insert('glpi_tickets', [
                    'entities_id'           => 0,
                    'name'                  => $DB->escape($title),
                    'content'               => $DB->escape('<p>' . nl2br(htmlspecialchars($content)) . '</p>'),
                    'date'                  => date('Y-m-d H:i:s'),
                    'date_creation'         => date('Y-m-d H:i:s'),
                    'date_mod'              => date('Y-m-d H:i:s'),
                    'status'                => 1,
                    'type'                  => 1,
                    'priority'              => 3,
                    'urgency'               => 3,
                    'impact'                => 3,
                    'requesttypes_id'       => 1,
                    'users_id_lastupdater'  => 0,
                    'is_deleted'            => 0,
                ]);
                $glpiId = (int)$DB->insertId();

                // Salva no SOLPI
                $DB->insert('glpi_plugin_solpi_tickets', [
                    'glpi_ticket_id' => $glpiId,
                    'status'         => 'OPEN',
                    'opened_at'      => date('Y-m-d H:i:s'),
                ]);

                $created++;
            } catch (Throwable $e) {
                $errors[] = "Linha " . ($i + 2) . ": " . $e->getMessage();
            }
        }

        @unlink($tmpFile);
    }

    echo '<div class="container-fluid py-3">';
    echo '<h3>Importação Concluída</h3>';
    echo '<div class="alert alert-success"><strong>' . $created . ' chamados criados com sucesso!</strong></div>';
    if ($errors) {
        echo '<div class="alert alert-warning"><strong>Erros:</strong><ul>';
        foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>';
        echo '</ul></div>';
    }
    echo '<a href="import.php" class="btn btn-primary">Nova Importação</a>';
    echo ' <a href="../../../front/ticket.php" class="btn btn-secondary">Ver Chamados no GLPI</a>';
    echo '</div>';
    Html::footer();
    exit;
}

// =============================================
// STEP 2: preview e mapeamento de colunas
// =============================================
if ($step === 'preview' && isset($_FILES['excel'])) {
    $tmpFile = $uploadDir . uniqid('import_') . '.xlsx';
    move_uploaded_file($_FILES['excel']['tmp_name'], $tmpFile);

    try {
        $rows    = $parser->parse($tmpFile);
        $headers = array_keys($rows[0] ?? []);
        $mapping = $detector->detect($headers);
        $preview = array_slice($rows, 0, 5);

        $fields = ['empresa','nome','telefone','email','problema','prioridade','categoria','serie','local','tecnico','status'];
    ?>
    <div class="container-fluid py-3">
        <h3>Mapeamento de Colunas — <?= htmlspecialchars($_FILES['excel']['name']) ?></h3>
        <p class="text-muted"><?= count($rows) ?> linhas detectadas. Verifique o mapeamento abaixo e clique em Importar.</p>

        <form method="post" action="import.php?step=import">
        <input type="hidden" name="tmp_file" value="<?= htmlspecialchars($tmpFile) ?>">

        <div class="card mb-4">
            <div class="card-header fw-bold">Mapeamento de Colunas</div>
            <div class="card-body">
                <table class="table table-sm table-bordered" style="max-width:600px">
                    <tr><th>Coluna do Excel</th><th>Campo SOLPI/GLPI</th></tr>
                    <?php foreach ($headers as $col): ?>
                    <tr>
                        <td><?= htmlspecialchars($col) ?></td>
                        <td>
                            <select name="mapping[<?= htmlspecialchars($col) ?>]" class="form-select form-select-sm">
                                <option value="">— ignorar —</option>
                                <?php foreach ($fields as $f): ?>
                                <option value="<?= $f ?>" <?= ($mapping[$col] ?? '') === $f ? 'selected' : '' ?>>
                                    <?= $f ?>
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
                    <thead><tr>
                        <?php foreach ($headers as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($preview as $row): ?>
                        <tr><?php foreach ($row as $val): ?><td><?= htmlspecialchars(mb_strimwidth($val,0,50,'...')) ?></td><?php endforeach; ?></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg">
            ✅ Importar <?= count($rows) ?> chamados para o GLPI
        </button>
        <a href="import.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
    <?php
    } catch (Throwable $e) {
        echo '<div class="container-fluid py-3"><div class="alert alert-danger">Erro ao ler arquivo: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
    }
    Html::footer();
    exit;
}

// =============================================
// STEP 1: tela de upload
// =============================================
?>
<div class="container-fluid py-3" style="max-width:700px">

    <h3 class="mb-1">📊 Importar Excel para GLPI</h3>
    <p class="text-muted mb-4">Faça upload de uma planilha Excel (.xlsx) e o SOLPI cria os chamados automaticamente, detectando empresa, solicitante, telefone e descrição do problema.</p>

    <div class="card">
        <div class="card-body">
            <form method="post" action="import.php?step=preview" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-bold">Arquivo Excel (.xlsx)</label>
                    <input type="file" name="excel" class="form-control" accept=".xlsx,.xls" required>
                    <small class="text-muted">Primeira linha deve ser o cabeçalho (ex: Empresa, Nome, Telefone, Problema)</small>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">📂 Carregar e Mapear Colunas</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header fw-bold">Colunas reconhecidas automaticamente</div>
        <div class="card-body">
            <div class="row">
                <?php
                $examples = [
                    'empresa' => 'Empresa, Company, Cliente, CNPJ',
                    'nome'    => 'Nome, Solicitante, Contato, Usuário',
                    'telefone'=> 'Telefone, Celular, WhatsApp',
                    'email'   => 'Email, E-mail, Mail',
                    'problema'=> 'Problema, Descrição, Chamado, Assunto, Título',
                    'prioridade' => 'Prioridade, Urgência, SLA',
                    'categoria'  => 'Categoria, Tipo, Setor',
                ];
                foreach ($examples as $field => $cols):
                ?>
                <div class="col-md-6 mb-2">
                    <small><strong><?= $field ?></strong>: <span class="text-muted"><?= $cols ?></span></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
<?php
Html::footer();