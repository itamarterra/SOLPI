<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class LicenseExtractor
{
    private array $licenses = [

        'BITDEFENDER',

        'WINDOWS',

        'OFFICE',

        'MICROSOFT 365',

        'ESET',

        'KASPERSKY'

    ];

    public function extract(
        string $text
    ): ?string {

        $text = mb_strtoupper($text);

        foreach ($this->licenses as $license) {

            if (str_contains($text, $license)) {

                return $license;

            }

        }

        return null;
    }

    public function add(
        string $license
    ): void {

        $license = mb_strtoupper($license);

        if (!in_array($license, $this->licenses, true)) {

            $this->licenses[] = $license;

        }
    }
}