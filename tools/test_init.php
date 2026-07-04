<?php
// NÃO definir GLPI_ROOT aqui — deixar o vendor autoload do GLPI fazer isso
require_once '/var/www/glpi/vendor/autoload.php';  // GLPI define GLPI_ROOT aqui
require_once '/var/glpi/config/config_db.php';

global $PLUGIN_HOOKS;
$PLUGIN_HOOKS = [];

echo "GLPI_ROOT: " . GLPI_ROOT . PHP_EOL;
echo "Loading setup.php..." . PHP_EOL;
require_once '/var/www/glpi/plugins/solpi/setup.php';

echo "plugin_init_solpi: " . (function_exists('plugin_init_solpi') ? 'OK' : 'MISSING') . PHP_EOL;

try {
    plugin_init_solpi();
    echo "plugin_init_solpi(): SUCCESS" . PHP_EOL;
    echo "Hooks registered: " . implode(', ', array_keys($PLUGIN_HOOKS)) . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}