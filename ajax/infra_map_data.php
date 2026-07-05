<?php

declare(strict_types=1);

/**
 * Endpoint AJAX para fornecer o mapa global de infraestrutura do Digital Twin.
 */

define('GLPI_ROOT', dirname(__DIR__, 3));
require_once GLPI_ROOT . '/inc/includes.php';

header('Content-Type: application/json');

// Segurança GLPI
Session::checkLoginUser();

$service = new \SOLPI\Modules\Infrastructure\Services\InfraVisualizationService();
$data = $service->getGlobalMap();

echo json_encode($data);
