<?php
require_once '/var/www/glpi/vendor/autoload.php';
echo class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ? "PhpSpreadsheet: OK\n" : "PhpSpreadsheet: NAO DISPONIVEL\n";
echo class_exists('PhpOffice\PhpSpreadsheet\Reader\Xlsx') ? "Reader XLSX: OK\n" : "Reader XLSX: NAO DISPONIVEL\n";
echo class_exists('PhpOffice\PhpSpreadsheet\Reader\Csv')  ? "Reader CSV: OK\n"  : "Reader CSV: NAO DISPONIVEL\n";