<?php
require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/inc/includes.php';

echo "Plugin loaded: " . (Plugin::isPluginLoaded('solpi') ? 'YES' : 'NO') . PHP_EOL;
echo "Plugin active: " . (Plugin::isPluginActive('solpi') ? 'YES' : 'NO') . PHP_EOL;

$p = new Plugin();
$p->getFromDBbyDir('solpi');
echo "State: " . ($p->fields['state'] ?? 'NOT FOUND') . PHP_EOL;