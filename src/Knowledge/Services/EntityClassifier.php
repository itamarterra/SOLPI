<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class EntityClassifier
{
    public function classify(
        array $data
    ): string {

        $text = strtoupper(
            implode(
                ' ',
                $data
            )
        );

        if (str_contains($text,'NOTEBOOK')) {
            return 'NOTEBOOK';
        }

        if (str_contains($text,'DESKTOP')) {
            return 'DESKTOP';
        }

        if (str_contains($text,'IMPRESSORA')) {
            return 'PRINTER';
        }

        if (str_contains($text,'MONITOR')) {
            return 'MONITOR';
        }

        if (str_contains($text,'LICENÇA')) {
            return 'LICENSE';
        }

        if (str_contains($text,'SERVIDOR')) {
            return 'SERVER';
        }

        if (str_contains($text,'ROTEADOR')) {
            return 'ROUTER';
        }

        return 'UNKNOWN';
    }
}
