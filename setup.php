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
                'min' => '10.0.0',
                'max' => '13.9.99'
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

    $loader = __DIR__ . '/vendor/autoload.php';
    if (is_file($loader)) {
        require_once $loader;
    }

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

    $PLUGIN_HOOKS['add_tabs']['solpi'] = [
        'Ticket' => 'PluginSolpiIncidentGraph'
    ];

    // Hook principal: disparado quando tecnico adiciona solucao ao ticket
    $PLUGIN_HOOKS['item_add']['solpi'] = [
        'ITILSolution' => 'plugin_solpi_on_solution_added',
        'Ticket'       => 'plugin_solpi_index_ticket'
    ];

    // Fallback: mudanca manual de status para Resolvido
    $PLUGIN_HOOKS['item_update']['solpi'] = [
        'Ticket' => 'plugin_solpi_index_ticket'
    ];

    if (file_exists(__DIR__ . '/hook.php')) {
        require_once __DIR__ . '/hook.php';
    }
}

function plugin_solpi_display_central(): void
{
    if (!Session::haveRight('config', READ) && !Session::haveRight('ticket', READ)) {
        return;
    }

    echo '<tr>';
    echo '<td class="center" style="padding:12px;">';
    echo '<div style="max-width:980px;margin:0 auto;background:#f8fbff;border:1px solid #dbeafe;border-radius:10px;padding:14px 16px;">';
    echo '<div style="font-weight:700;margin-bottom:6px;">SOLPI</div>';
    echo '<div style="margin-bottom:10px;">Janela SOLPI para importar dados e gerar chamados automaticamente.</div>';
    echo '<a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="/solpi-import.php">Abrir Janela SOLPI</a>';
    echo '</div>';
    echo '</td>';
    echo '</tr>';
}

require_once __DIR__ . '/inc/install.php';
require_once __DIR__ . '/inc/uninstall.php';