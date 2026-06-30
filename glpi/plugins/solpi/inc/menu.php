<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    exit;
}

function plugin_menu_solpi(array $menus): array
{
    $menus['solpi'] = [
        'title' => __('SOLPI Professional', 'solpi'),
        'page'  => '/plugins/solpi/front/index.php',
        'icon'  => 'fa fa-cogs',
    ];

    return $menus;
}

function plugin_permissions_solpi(): array
{
    return [
        'solpi_manage' => [
            'name'  => __('Manage SOLPI', 'solpi'),
            'title' => __('Manage plugin settings and features', 'solpi'),
        ],
    ];
}

