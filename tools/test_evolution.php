<?php
require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

global $DB;
$DB = new DB();

$cfg = new SOLPI\Core\Config();
$cfg->load();
$ev = $cfg->get('evolution', []);

echo "enabled: " . var_export($ev['enabled'] ?? null, true) . PHP_EOL;
echo "base_url: " . ($ev['base_url'] ?? 'NOT SET') . PHP_EOL;
echo "auth_key: " . ($ev['auth_key'] ?? 'NOT SET') . PHP_EOL;
echo "instance: " . ($ev['instance'] ?? 'NOT SET') . PHP_EOL;

$client = new SOLPI\Integrations\Evolution\EvolutionClient($ev);
$result = $client->sendText('5519999904710', 'Chamado #TEST criado com sucesso pelo SOLPI!');
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;