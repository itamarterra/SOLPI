<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class ManufacturerDetector
{
    private array $manufacturers = [

        'DELL',

        'HP',

        'LENOVO',

        'EPSON',

        'BROTHER',

        'CANON',

        'LG',

        'AOC',

        'SMS',

        'APC',

        'MIKROTIK',

        'TP-LINK',

        'INTEL',

        'AMD'

    ];

    public function detect(
        string $text
    ): ?string {

        $text = strtoupper($text);

        foreach ($this->manufacturers as $manufacturer) {

            if (str_contains($text,$manufacturer)) {

                return $manufacturer;

            }

        }

        return null;
    }
}
