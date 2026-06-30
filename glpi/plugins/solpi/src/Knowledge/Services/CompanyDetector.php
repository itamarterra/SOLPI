<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class CompanyDetector
{
    public function detect(
        string $text
    ): ?string {

        $companies = [

            'TRELICAMP',

            'OCTIO',

            'ECIN'

        ];

        $text = strtoupper($text);

        foreach ($companies as $company) {

            if (str_contains($text,$company)) {

                return $company;

            }

        }

        return null;

    }
}
