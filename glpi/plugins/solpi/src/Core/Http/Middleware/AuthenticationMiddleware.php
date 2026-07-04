<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Middleware;

final class AuthenticationMiddleware
{
    public function handle(): bool
    {
        // No GLPI, a sessão é gerenciada globalmente. 
        // Verificamos se há um ID de usuário na sessão.
        if (isset($_SESSION['glpiID']) && $_SESSION['glpiID'] > 0) {
            return true;
        }
        
        // Em modo CLI ou API, pode haver outros métodos
        return defined('GLPI_CLI') && GLPI_CLI;
    }
}

