<?php

declare(strict_types=1);

define('PLUGIN_SOLPI_VERSION', '2.0.0');

/**
 * Informações do plugin
 */
function plugin_version_solpi(): array
{
    return [
        'name'           => 'SOLPI Professional',
        'version'        => PLUGIN_SOLPI_VERSION,
        'author'         => 'Itamar Terra',
        'license'        => 'MIT',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0.0',
                'max' => '11.9.99'
            ]
        ]
    ];
}

/**
 * Inicialização do plugin
 */
function plugin_init_solpi(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['solpi'] = true;
    $PLUGIN_HOOKS['menu_entry']['solpi'] = 'front/index.php';
    $PLUGIN_HOOKS['config_page']['solpi'] = 'front/config.php';
    $PLUGIN_HOOKS['display_central']['solpi'] = 'plugin_solpi_display_central';
    $importMenuFile = __DIR__ . '/src/Menu/ImportMenu.php';
    if (is_file($importMenuFile)) {
        require_once $importMenuFile;
    }
    $PLUGIN_HOOKS['menu_toadd']['solpi'] = [
        'plugins' => SOLPI\Menu\ImportMenu::class,
    ];

    // Hook principal: disparado quando tecnico adiciona solucao ao ticket
    $PLUGIN_HOOKS['item_add']['solpi'] = ['ITILSolution' => 'plugin_solpi_on_solution_added'];

    // Fallback: mudanca manual de status para Resolvido
    $PLUGIN_HOOKS['item_update']['solpi'] = ['Ticket' => 'plugin_solpi_on_ticket_update'];

    if (file_exists(__DIR__ . '/hook.php')) {
        require_once __DIR__ . '/hook.php';
    }
}

function plugin_solpi_display_central(): void
{
    if (!Session::haveRight('config', READ) && !Session::haveRight('ticket', READ)) {
        return;
    }

    echo '<section class="card mb-3">';
    echo '<div class="card-header"><strong>SOLPI</strong></div>';
    echo '<div class="card-body">';
    echo '<p class="mb-2">Janela SOLPI para importar dados e gerar chamados automaticamente.</p>';
    echo '<a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="' . GLPI_ROOT . '/solpi-import.php">Abrir Janela SOLPI</a>';
    echo '</div>';
    echo '</section>';
}

require_once __DIR__ . '/inc/install.php';
require_once __DIR__ . '/inc/uninstall.php';