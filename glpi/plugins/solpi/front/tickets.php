<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

$rows = [];
$error = null;

global $DB;

if ($DB) {
    try {
        foreach ($DB->request([
            'SELECT' => 'id, name, status',
            'FROM'   => 'glpi_tickets',
            'ORDER'  => 'id DESC',
            'LIMIT'  => 50,
        ]) as $row) {
            $rows[] = $row;
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
} else {
    $error = 'Conexão com o banco de dados GLPI indisponível.';
}

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>Tickets SOLPI</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';

