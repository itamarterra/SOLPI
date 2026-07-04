<?php

declare(strict_types=1);

namespace SOLPI\Core;

/**
 * Classe base para todos os Controllers.
 */
abstract class Controller
{
    protected function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $templateFile = dirname(__DIR__, 2)
            . '/templates/'
            . $template
            . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException(
                "Template {$template} não encontrado."
            );
        }

        require $templateFile;
    }

    protected function json(array $data): never
    {
        header('Content-Type: application/json');

        echo json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}