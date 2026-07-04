<?php

declare(strict_types=1);

namespace SOLPI\Menu;

if (!defined('GLPI_ROOT')) {
    exit;
}

use CommonGLPI;
use Session;

use function __;

final class ImportMenu extends \CommonGLPI
{
    public static $rightname = 'config';

    public static function getTypeName($nb = 0)
    {
        return __('Janela de Importação SOLPI', 'solpi');
    }

    public static function canView(): bool
    {
        return Session::haveRight('solpi_view', 1)
            || Session::haveRight('solpi_manage', 1)
            || Session::haveRight('config', 1)
            || Session::haveRight('ticket', 1);
    }

    public static function getMenuContent(): array
    {
        if (!self::canView()) {
            return [];
        }

        return [
            'title' => self::getTypeName(),
            'page'  => '/solpi-import.php',
            'icon'  => 'ti ti-file-arrow-up',
        ];
    }
}