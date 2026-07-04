<?php
require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';
global $DB; $DB = new DB();

$ctrl  = new SOLPI\Modules\WhatsApp\WhatsAppController();
$phone = '5519999904710';

function step(string $title, array $result): void {
    echo PHP_EOL . "=== $title ===" . PHP_EOL;
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
}

function msg(string $phone, string $text): array {
    return [
        'event' => 'messages.upsert', 'instance' => 'solpi', 'apikey' => 'solpi123',
        'data'  => [
            'key'     => ['remoteJid' => $phone . '@s.whatsapp.net', 'fromMe' => false, 'id' => uniqid()],
            'message' => ['conversation' => $text],
            'pushName' => 'Keyne',
        ],
    ];
}

step("1. Abre chamado", $ctrl->handleWebhook(msg($phone, 'Meu monitor nao liga')));
step("2. Responde SIM (fecha + pede avaliacao)", $ctrl->handleWebhook(msg($phone, 'SIM')));
step("3. Avalia com 5 estrelas", $ctrl->handleWebhook(msg($phone, '5')));

// Resultado final no banco
$repo = new SOLPI\Modules\Tickets\TicketRepository();
$t = $repo->findByPhone($phone);
echo PHP_EOL . "=== RESULTADO FINAL ===" . PHP_EOL;
echo "Status SOLPI: " . ($t['status'] ?? '-') . PHP_EOL;
echo "Rating: " . ($t['rating'] ?? 'nenhum') . " estrelas" . PHP_EOL;
foreach ($DB->request(['FROM'=>'glpi_tickets','WHERE'=>['id'=>$t['glpi_ticket_id'] ?? 0]]) as $g) {
    echo "Status GLPI: " . $g['status'] . " (6=Fechado)" . PHP_EOL;
}