<?php

declare(strict_types=1);

/**
 * Endpoint AJAX para fornecer dados do grafo em formato JSON
 */

define('GLPI_ROOT', dirname(__DIR__, 3));
require_once GLPI_ROOT . '/inc/includes.php';

header('Content-Type: application/json');

// Verificação básica de segurança
Session::checkLoginUser();

$ticketId = (int)($_GET['tickets_id'] ?? 0);
if ($ticketId <= 0) {
    echo json_encode(['nodes' => [], 'edges' => []]);
    exit;
}

$vizService = new \SOLPI\Modules\Intelligence\Services\GraphVisualizationService();
$data = $vizService->getGraphDataForTicket($ticketId);

echo json_encode($data);
