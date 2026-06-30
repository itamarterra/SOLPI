<?php
// Script de teste do webhook SOLPI
// Uso: php tools/test_webhook.php

require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

global $DB;
$DB = new DB();

$payload = [
    'apikey'   => 'solpi123',
    'event'    => 'messages.upsert',
    'instance' => 'solpi',
    'data'     => [
        'key'     => ['remoteJid' => '5519981584722@s.whatsapp.net', 'fromMe' => false, 'id' => 'TEST123'],
        'message' => ['conversation' => 'Meu notebook nao liga desde ontem'],
        'pushName' => 'Itamar Terra',
    ],
];

$controller = new SOLPI\Modules\WhatsApp\WhatsAppController();
$result = $controller->handleWebhook($payload);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;