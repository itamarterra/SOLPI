<?php
// Debug do output buffering
$log = '/tmp/solpi_test.log';
file_put_contents($log, "START ob_level=" . ob_get_level() . "\n");
echo 'PASSO1 ';
file_put_contents($log, "AFTER_ECHO ob_level=" . ob_get_level() . "\n", FILE_APPEND);
include __DIR__ . '/../inc/includes.php';
file_put_contents($log, "AFTER_INCLUDE ob_level=" . ob_get_level() . "\n", FILE_APPEND);
echo 'PASSO2 ';
file_put_contents($log, "AFTER_ECHO2 ob_level=" . ob_get_level() . "\n", FILE_APPEND);