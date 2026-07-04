<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>Alertas SOLPI</h1>
        <p>Exibe os alertas capturados pelo plugin SOLPI.</p>
        <div class="alert alert-info">Nenhum alerta ativo encontrado no momento.</div>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';

