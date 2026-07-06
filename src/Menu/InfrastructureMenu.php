<?php

declare(strict_types=1);

namespace SOLPI\Menu;

if (!defined('GLPI_ROOT')) {
    exit;
}

use CommonGLPI;
use Session;

final class InfrastructureMenu extends \CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return 'SOLPI Explorer';
    }

    public static function canView(): bool
    {
        return true;
    }

    public static function getMenuContent(): array
    {
        return [
            'title' => 'SOLPI Explorer',
            'page'  => '/plugins/solpi/front/infrastructure.php',
            'icon'  => 'ti ti-topology-complex',
        ];
    }
}
