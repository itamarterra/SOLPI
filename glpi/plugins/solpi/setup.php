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

    if (file_exists(__DIR__ . '/hook.php')) {
        require_once __DIR__ . '/hook.php';
    }
}
require_once __DIR__ . '/inc/install.php';
require_once __DIR__ . '/inc/uninstall.php';