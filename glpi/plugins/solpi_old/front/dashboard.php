
<?php

include('../../../inc/includes.php');

Html::header(
   'SOLPI Dashboard',
   $_SERVER['PHP_SELF'],
   'tools',
   'pluginssolpi'
);

echo "<div style='padding:20px'>";

echo "<h1>SOLPI Dashboard</h1>";

echo "<h3>Status</h3>";

echo "<ul>";
echo "<li>GLPI: Online</li>";
echo "<li>Zabbix: Aguardando Integração</li>";
echo "<li>WhatsApp: Aguardando Integração</li>";
echo "</ul>";

echo "</div>";

Html::footer();
