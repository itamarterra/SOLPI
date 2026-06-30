<?php
require_once '/var/www/glpi/vendor/autoload.php';
require_once '/var/glpi/config/config_db.php';
require_once '/var/www/glpi/plugins/solpi/vendor/autoload.php';

global $DB;
$DB = new DB();

require_once '/var/www/glpi/plugins/solpi/hook.php';

// Busca o ticket SOLPI mais recente com chamado aberto
$repo = new SOLPI\Modules\Tickets\TicketRepository();
$phone = '5519999904710';

// Cria um ticket de teste primeiro
$ctrl = new SOLPI\Modules\WhatsApp\WhatsAppController();
$r = $ctrl->handleWebhook([
    'event' => 'messages.upsert', 'instance' => 'solpi', 'apikey' => 'solpi123',
    'data'  => [
        'key'     => ['remoteJid' => $phone . '@s.whatsapp.net', 'fromMe' => false, 'id' => uniqid()],
        'message' => ['conversation' => 'Minha internet caiu'],
        'pushName' => 'Keyne',
    ],
]);
$glpiId = $r['glpi_ticket_id'];
echo "Ticket #$glpiId aberto via WhatsApp" . PHP_EOL;

// Simula a adicao de solucao pelo tecnico (como se fosse o GLPI chamando o hook)
$fakeSolution = new stdClass();
$fakeSolution->fields = [
    'itemtype' => 'Ticket',
    'items_id' => $glpiId,
    'content'  => 'Reiniciamos o roteador e a internet voltou normalmente.',
    'status'   => 2,
    'id'       => 999,
];

// Casting para ITILSolution usando Reflection para simular o instanceof
// Em vez disso, chamamos a funcao diretamente com um objeto real de ITILSolution
$solution = new ITILSolution();
$solution->fields = $fakeSolution->fields;

$result = plugin_solpi_on_solution_added(['item' => $solution]);
echo "Hook executado. Verificando mensagem enviada..." . PHP_EOL;

// Verifica
foreach ($DB->request([
    'FROM'  => 'glpi_plugin_solpi_whatsapp',
    'WHERE' => ['direction' => 'OUTBOUND'],
    'ORDER' => 'id DESC',
    'LIMIT' => 2,
]) as $row) {
    echo "OUTBOUND [{$row['status']}]: " . mb_strimwidth($row['message'], 0, 80) . PHP_EOL;
}

// Status do ticket SOLPI
$t = $repo->findByGLPITicketId($glpiId);
echo "Status SOLPI ticket: " . ($t['status'] ?? '-') . PHP_EOL;