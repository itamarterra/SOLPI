<?php
define('GLPI_ROOT', '/var/www/glpi');
require_once GLPI_ROOT . '/inc/includes.php';
require_once GLPI_ROOT . '/plugins/solpi/vendor/autoload.php';

$cfg = new \SOLPI\Core\Config();
$cfg->load();
$z = $cfg->get('config.zabbix');
$token = trim($z['token']);
$url = $z['base_url'] . '/api_jsonrpc.php';

echo "URL: $url\n";
echo "Token (primeiros 10): " . substr($token, 0, 10) . "...\n";

function callZbxFinal($url, $token, $method, $params = []) {
    $data = [
        "jsonrpc" => "2.0",
        "method" => $method,
        "params" => $params,
        "id" => 1
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    ]);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    echo "HTTP Status: " . $info['http_code'] . "\n";
    return json_decode($res, true);
}

echo "\n--- Test Final: host.get with Bearer Authorization ---\n";
$res = callZbxFinal($url, $token, "host.get", ["output" => ["hostid", "name"], "limit" => 1]);
echo "Result: " . json_encode($res, JSON_PRETTY_PRINT) . "\n";
