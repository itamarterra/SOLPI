<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class AssetExtractor
{
    private array $types = [

        'NOTEBOOK',

        'DESKTOP',

        'SERVIDOR',

        'IMPRESSORA',

        'MONITOR',

        'NOBREAK',

        'SWITCH',

        'ROTEADOR',

        'FIREWALL'

    ];

    public function extract(
        string $text
    ): ?string {

        $upper = mb_strtoupper($text);

        foreach ($this->types as $type) {

            if (str_contains($upper, $type)) {

                return $type;

            }

        }

        return null;
    }

    public function supported(): array
    {
        return $this->types;
    }
}