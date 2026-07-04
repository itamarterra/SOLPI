<?php
require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

global $DB;
$DB = new DB();

$ctrl = new SOLPI\Modules\WhatsApp\WhatsAppController();

// Usa o numero que realmente abriu os chamados
$phone = '5519999904710';

// Mostra o ticket mais recente antes
$repo = new SOLPI\Modules\Tickets\TicketRepository();
$ticket = $repo->findByPhone($phone);
echo "Ticket encontrado: " . json_encode($ticket ? ['id'=>$ticket['id'],'glpi_id'=>$ticket['glpi_ticket_id'],'status'=>$ticket['status']] : null) . PHP_EOL;

// Simula mensagem "SIM"
$payload = [
    'event'    => 'messages.upsert',
    'instance' => 'solpi',
    'apikey'   => 'solpi123',
    'data'     => [
        'key'     => ['remoteJid' => $phone . '@s.whatsapp.net', 'fromMe' => false, 'id' => 'SIM_TEST_2'],
        'message' => ['conversation' => 'SIM'],
        'pushName' => 'Teste',
    ],
];

$result = $ctrl->handleWebhook($payload);
echo "Resultado: " . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;

// Status apos
foreach ($DB->request(['FROM'=>'glpi_tickets','ORDER'=>'id DESC','LIMIT'=>1]) as $g) {
    echo "GLPI ticket #" . $g['id'] . " status: " . $g['status'] . " (1=Aberto, 6=Fechado)" . PHP_EOL;
}