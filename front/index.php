<?php

declare(strict_types=1);

include __DIR__ . '/../../inc/includes.php';

Session::checkLoginUser();

include __DIR__ . '/../templates/layouts/header.php';
?>
<div class="row">
    <div class="col-12">
        <h1>Bem-vindo ao SOLPI Professional</h1>
        <p>Este plugin integra GLPI com Zabbix, WhatsApp e automação de tickets.</p>
        <div class="alert alert-info border-0 shadow-sm mb-3">
            <strong>Atalho principal:</strong> abra a <a href="/solpi-import.php" target="_blank" rel="noopener noreferrer" class="alert-link">Janela de Importação SOLPI</a> em nova aba para receber planilhas, textos copiados e dados de outros sites.
        </div>
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="tickets.php" class="list-group-item list-group-item-action">Tickets SOLPI</a>
            <a href="/solpi-import.php" target="_blank" rel="noopener noreferrer" class="list-group-item list-group-item-action">Janela de Importação SOLPI (nova aba)</a>
            <a href="glpi_tickets.php" class="list-group-item list-group-item-action">Chamados GLPI</a>
            <a href="alerts.php" class="list-group-item list-group-item-action">Alertas</a>
            <a href="whatsapp.php" class="list-group-item list-group-item-action">WhatsApp</a>
            <a href="config.php" class="list-group-item list-group-item-action">Configurações</a>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../templates/layouts/footer.php';

