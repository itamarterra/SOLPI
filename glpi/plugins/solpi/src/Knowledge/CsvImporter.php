<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class CsvImporter
{
    public function import(
        string $csv
    ): array {

        $rows = [];

        $lines = preg_split('/\r\n|\r|\n/', $csv);

        foreach ($lines as $line) {

            if ($line === '') {
                continue;
            }

            $rows[] = str_getcsv(
                $line,
                ';'
            );

        }

        return $rows;
    }
}