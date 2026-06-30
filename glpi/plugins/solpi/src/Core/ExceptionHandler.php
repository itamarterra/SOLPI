<?php

declare(strict_types=1);

namespace SOLPI\Core;

use Throwable;

/**
 * Tratamento global de exceções.
 */
final class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler(
            function (Throwable $exception): void {

                $logger = new Logger();

                $logger->error(
                    sprintf(
                        '%s (%s:%d)',
                        $exception->getMessage(),
                        $exception->getFile(),
                        $exception->getLine()
                    )
                );

                http_response_code(500);

                echo "<h2>SOLPI Professional</h2>";
                echo "<p>Ocorreu um erro interno.</p>";
            }
        );
    }
}