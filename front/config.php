<?php

declare(strict_types=1);

try {
    include __DIR__ . '/../inc/includes.php';

    // Verificação de segurança padrão do GLPI
    Session::checkRight('config', UPDATE);

    // Cabeçalho nativo do GLPI
    Html::header('SOLPI Professional', $_SERVER['PHP_SELF'], 'config', 'plugin_solpi_config');

    $constantsFile = __DIR__ . '/../config/constants.php';
    $configFile = __DIR__ . '/../config/config.php';

    $constants = file_exists($constantsFile) ? require $constantsFile : [];
    $config = file_exists($configFile) ? require $configFile : [];

    // Mapeamento correto das chaves do config.php
    $settings = [
        'Plugin Name' => $constants['solpi_name'] ?? 'SOLPI',
        'Plugin Version' => $constants['solpi_version'] ?? 'unknown',
        'AI Enabled' => ($config['ai']['enabled'] ?? false) ? 'Sim' : 'Não',
        'AI Provider' => $config['ai']['provider'] ?? 'N/A',
        'Zabbix Enabled' => ($config['zabbix']['enabled'] ?? false) ? 'Sim' : 'Não',
        'WhatsApp Enabled' => ($config['evolution']['enabled'] ?? false) ? 'Sim' : 'Não',
        'WhatsApp URL' => $config['evolution']['base_url'] ?? 'N/A',
    ];

    echo "<div class='container-fluid' style='padding: 20px;'>";
    echo "<h2>Configurações do Plugin SOLPI</h2>";

    echo "<table class='tab_cadre_fixehov'>";
    echo "<thead><tr><th>Parâmetro</th><th>Valor</th></tr></thead>";
    echo "<tbody>";

    foreach ($settings as $label => $value) {
        echo "<tr>";
        echo "<td><strong>$label</strong></td>";
        echo "<td>" . htmlspecialchars((string)$value) . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "</div>";

    // Rodapé nativo do GLPI
    Html::footer();

} catch (Throwable $e) {
    echo "<div style='color: red; background: #fff0f0; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h3>Erro ao carregar configurações do SOLPI</h3>";
    echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . " (Linha: " . $e->getLine() . ")</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
