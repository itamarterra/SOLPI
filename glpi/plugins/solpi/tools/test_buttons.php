<?php
function sendButtons(string $number, string $text, array $buttons): array {
    $url  = 'http://evolution-api:8080/message/sendButtons/solpi';
    $body = json_encode([
        'number'   => $number,
        'title'    => 'Avaliacao',
        'description' => $text,
        'footer'   => 'SOLPI',
        'buttons'  => $buttons,
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: solpi123','Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($resp, true)];
}

$phone = '5519999904710';

// Mensagem 1: notas 1, 2, 3
$r1 = sendButtons($phone, 'Como voce avalia o atendimento? (parte 1/2)', [
    ['buttonId' => '1', 'buttonText' => ['displayText' => '1 ⭐ Pessimo']],
    ['buttonId' => '2', 'buttonText' => ['displayText' => '2 ⭐⭐ Ruim']],
    ['buttonId' => '3', 'buttonText' => ['displayText' => '3 ⭐⭐⭐ Regular']],
]);
echo "Msg1 HTTP {$r1['code']}: " . json_encode($r1['body']['status'] ?? $r1['body']['error'] ?? $r1['body']) . PHP_EOL;

// Mensagem 2: notas 4, 5
$r2 = sendButtons($phone, 'Como voce avalia o atendimento? (parte 2/2)', [
    ['buttonId' => '4', 'buttonText' => ['displayText' => '4 ⭐⭐⭐⭐ Bom']],
    ['buttonId' => '5', 'buttonText' => ['displayText' => '5 ⭐⭐⭐⭐⭐ Excelente']],
]);
echo "Msg2 HTTP {$r2['code']}: " . json_encode($r2['body']['status'] ?? $r2['body']['error'] ?? $r2['body']) . PHP_EOL;