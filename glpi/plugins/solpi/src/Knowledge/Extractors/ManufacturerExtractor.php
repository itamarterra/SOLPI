<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Extractors;

final class ManufacturerExtractor
{
    private array $manufacturers = [

        'DELL',

        'HP',

        'LENOVO',

        'EPSON',

        'BROTHER',

        'CANON',

        'ELGIN',

        'SAMSUNG',

        'LG',

        'AOC',

        'MIKROTIK',

        'TP-LINK',

        'TPLINK',

        'SMS',

        'APC',

        'INTEL',

        'AMD'

    ];

    public function extract(
        string $text
    ): ?string {

        $text = mb_strtoupper($text);

        foreach ($this->manufacturers as $manufacturer) {

            if (str_contains($text, $manufacturer)) {

                return $manufacturer;

            }

        }

        return null;
    }
}