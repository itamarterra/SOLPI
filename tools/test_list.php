<?php
$url = 'http://evolution-api:8080/message/sendList/solpi';
$body = json_encode([
    'number'      => '5519999904710',
    'title'       => 'Avaliacao do Atendimento',
    'description' => 'Como voce avalia o nosso atendimento? Toque para selecionar.',
    'buttonText'  => 'Ver opcoes',
    'footer'      => 'SOLPI Service Desk',
    'footerText'  => 'SOLPI Service Desk',
    'sections'    => [[
        'title' => 'Selecione sua nota',
        'rows'  => [
            ['title' => '5 - Excelente', 'description' => 'Fiquei muito satisfeito', 'rowId' => '5'],
            ['title' => '4 - Bom',       'description' => 'Fui bem atendido',        'rowId' => '4'],
            ['title' => '3 - Regular',   'description' => 'Atendimento ok',          'rowId' => '3'],
            ['title' => '2 - Ruim',      'description' => 'Poderia ser melhor',      'rowId' => '2'],
            ['title' => '1 - Pessimo',   'description' => 'Fiquei insatisfeito',     'rowId' => '1'],
        ],
    ]],
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: solpi123', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $code: $resp" . PHP_EOL;