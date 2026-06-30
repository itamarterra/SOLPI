<?php
// Arquivo montado em public/solpi-test.php - sem Symfony, sem sessao
file_put_contents('/tmp/public_test.log', "EXECUTOU " . date('H:i:s') . "\n");
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:20px">';
echo '<h1 style="color:green">SOLPI PUBLIC TEST OK</h1>';
echo '<p>PHP: ' . PHP_VERSION . '</p>';
echo '<p>Hora: ' . date('H:i:s') . '</p>';
echo '</body></html>';