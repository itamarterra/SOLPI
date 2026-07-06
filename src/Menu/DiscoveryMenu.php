<?php

declare(strict_types=1);

namespace SOLPI\Menu;

if (!defined('GLPI_ROOT')) {
    exit;
}

use CommonGLPI;
use Session;

final class DiscoveryMenu extends \CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return 'SOLPI Scan';
    }

    /**
     * Garante que o menu seja visível para administradores
     */
    public static function canView(): bool
    {
        return true;
    }

    public static function getMenuContent(): array
    {
        return [
            'title' => 'SOLPI Scan',
            'page'  => '/plugins/solpi/front/discovery.php',
            'icon'  => 'ti ti-search',
            'links' => [
                'search' => '/plugins/solpi/front/discovery.php',
                'add'    => '/plugins/solpi/front/discovery.php'
            ]
        ];
    }
}
