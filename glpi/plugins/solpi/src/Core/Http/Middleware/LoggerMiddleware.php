<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Middleware;

final class LoggerMiddleware
{
    public function log(string $message, string $level = 'INFO'): void
    {
        $logPath = GLPI_ROOT . '/files/_log/solpi.log';
        $entry = sprintf("[%s] [%s]: %s\n", date('Y-m-d H:i:s'), $level, $message);
        
        // Simulação de escrita em log (utilizando error_log se o caminho não estiver disponível)
        if (defined('GLPI_ROOT')) {
            @file_put_contents($logPath, $entry, FILE_APPEND);
        } else {
            error_log($entry);
        }
    }
}

