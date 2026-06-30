<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>WhatsApp SOLPI</h1>
        <p>Esta página mostra o status da integração WhatsApp.</p>
        <div class="alert alert-warning">A integração WhatsApp ainda está em desenvolvimento.</div>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';

