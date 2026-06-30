<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use Session;

final class DashboardPolicy
{
    /**
     * Verifica se o usuário autenticado pode visualizar
     * o Dashboard do SOLPI.
     */
    public function canView(): bool
    {
        if (!Session::getLoginUserID()) {
            return false;
        }

        return Session::haveRight('config', READ)
            || Session::haveRight('ticket', READ)
            || Session::haveRight('plugin_solpi', READ);
    }
}
