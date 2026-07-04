<?php

declare(strict_types=1);

/**
 * SOLPI - Bootstrapper corrigido
 */

if (!defined('GLPI_ROOT')) {
    // Tenta detectar a raiz subindo 3 níveis (plugins/solpi/inc -> raiz)
    $glpi_root = dirname(__DIR__, 3);
    define('GLPI_ROOT', $glpi_root);
}

// O segredo é garantir a barra "/" antes de "inc"
$include_path = GLPI_ROOT . '/inc/includes.php';

if (!file_exists($include_path)) {
    // Se falhar, tenta um ajuste preventivo para caminhos Windows/Linux
    $include_path = rtrim(GLPI_ROOT, '/\\') . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'includes.php';
}

if (!file_exists($include_path)) {
    die("Erro Fatal SOLPI: Não foi possível carregar o arquivo central do GLPI.<br>Caminho tentado: <b>{$include_path}</b>");
}

require_once $include_path;
