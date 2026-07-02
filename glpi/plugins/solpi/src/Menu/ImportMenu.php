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
        return Session::haveRight('solpi_view', \READ)
            || Session::haveRight('solpi_manage', \READ)
            || Session::haveRight('config', \READ)
            || Session::haveRight('ticket', \READ);
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