<?php

declare(strict_types=1);

include __DIR__ . '/../inc/includes.php';

Session::checkLoginUser();

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>Conhecimento SOLPI</h1>
        <p>O módulo de conhecimento do SOLPI reúne informações de tickets, alertas e soluções.</p>
        <ul>
            <li>Pesquisa de tickets recentes</li>
            <li>Documentação de soluções</li>
            <li>Histórico de ações</li>
        </ul>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';

