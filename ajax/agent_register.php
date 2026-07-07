<?php

declare(strict_types=1);

include('../../../inc/includes.php');
require_once GLPI_ROOT . '/plugins/solpi/vendor/autoload.php';

header('Content-Type: application/json');

$db = \SOLPI\Core\Database\DatabaseManager::getInstance();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['site_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$siteName = $input['site_name'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Verifica se já existe
$existing = $db->table('glpi_plugin_solpi_installations')->where(['site_name' => $siteName])->first();

if ($existing) {
    // Atualiza
    $db->table('glpi_plugin_solpi_installations')
       ->where(['id' => $existing['id']])
       ->update([
           'last_seen' => date('Y-m-d H:i:s'),
           'ip_address' => $ip,
           'status' => 'ONLINE'
       ]);
    echo json_encode(['success' => true, 'message' => 'Heartbeat atualizado.', 'id' => $existing['id']]);
} else {
    // Registra novo
    global $DB;
    $DB->insert('glpi_plugin_solpi_installations', [
        'site_name' => $siteName,
        'ip_address' => $ip,
        'solpi_version' => '1.0',
        'status' => 'ONLINE',
        'last_seen' => date('Y-m-d H:i:s')
    ]);
    echo json_encode(['success' => true, 'message' => 'Agente registrado com sucesso.', 'id' => $DB->insert_id()]);
}
