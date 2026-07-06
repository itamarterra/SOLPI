<?php

declare(strict_types=1);

define('PLUGIN_SOLPI_VERSION', '2.0.0');

function plugin_version_solpi(): array
{
    return [
        'name'           => 'SOLPI',
        'version'        => PLUGIN_SOLPI_VERSION,
        'author'         => 'Itamar Terra',
        'license'        => 'MIT',
        'requirements'   => [
            'glpi' => ['min' => '10.0.0', 'max' => '13.9.99']
        ]
    ];
}

function plugin_init_solpi(): void
{
    global $PLUGIN_HOOKS;

    $loader = __DIR__ . '/vendor/autoload.php';
    if (is_file($loader)) {
        require_once $loader;
    }

    $PLUGIN_HOOKS['csrf_compliant']['solpi'] = true;

    // Menu principal
    $PLUGIN_HOOKS['menu_entry']['solpi'] = 'front/index.php';
    $PLUGIN_HOOKS['config_page']['solpi'] = 'front/config.php';
    $PLUGIN_HOOKS['display_central']['solpi'] = 'plugin_solpi_display_central';

    // Registro das classes de menu
    $importMenuFile = __DIR__ . '/src/Menu/ImportMenu.php';
    $discoveryMenuFile = __DIR__ . '/src/Menu/DiscoveryMenu.php';
    $infraMenuFile = __DIR__ . '/src/Menu/InfrastructureMenu.php';

    if (is_file($importMenuFile)) require_once $importMenuFile;
    if (is_file($discoveryMenuFile)) require_once $discoveryMenuFile;
    if (is_file($infraMenuFile)) require_once $infraMenuFile;

    // Injeção nos menus oficiais do GLPI 11
    $PLUGIN_HOOKS['menu_toadd']['solpi'] = [
        'plugins' => SOLPI\Menu\ImportMenu::class,      // Fica em Plugins
        'tools'   => SOLPI\Menu\DiscoveryMenu::class,   // Fica em Ferramentas (Scan)
        'config'  => SOLPI\Menu\InfrastructureMenu::class, // Fica em Configuração (Explorer)
    ];

    $PLUGIN_HOOKS['add_tabs']['solpi'] = [
        'Ticket' => 'PluginSolpiIncidentGraph'
    ];

    $PLUGIN_HOOKS['item_add']['solpi'] = [
        'ITILSolution' => 'plugin_solpi_on_solution_added',
        'Ticket'       => 'plugin_solpi_index_ticket'
    ];

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
    echo '<tr><td class="center" style="padding:12px;">';
    echo '<div style="max-width:980px;margin:0 auto;background:#f8fbff;border:1px solid #dbeafe;border-radius:10px;padding:14px 16px;">';
    echo '<div style="font-weight:700;margin-bottom:6px;color:#0d6efd;">SOLPI INTELLIGENT</div>';
    echo '<div style="margin-bottom:10px;color:#64748b;">Acesse a central de importação de ativos e criação automática de chamados.</div>';
    echo '<a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="/solpi-import.php">Abrir Janela de Importação</a>';
    echo '</div></td></tr>';
}

require_once __DIR__ . '/inc/install.php';
require_once __DIR__ . '/inc/uninstall.php';
