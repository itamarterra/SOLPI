<?php

declare(strict_types=1);

namespace PhpOffice\PhpSpreadsheet;

if (!class_exists(Spreadsheet::class)) {
    class Spreadsheet
    {
        public function getActiveSheet(): Worksheet
        {
            return new Worksheet();
        }
    }
}

if (!class_exists(Worksheet::class)) {
    class Worksheet
    {
        /**
         * @return array<int,array<string,mixed>>
         */
        public function toArray(mixed $nullValue = null, bool $calculateFormulas = true, bool $formatData = true, bool $returnCellRef = true): array
        {
            return [];
        }

        /**
         * @return string
         */
        public function getHighestColumn(): string
        {
            return 'A';
        }

        /**
         * @return array<int,array<int,mixed>>
         */
        public function rangeToArray(string $range, mixed $nullValue = null, bool $calculateFormulas = true, bool $formatData = true, bool $returnCellRef = false): array
        {
            return [[]];
        }
    }
}

if (!class_exists(IOFactory::class)) {
    class IOFactory
    {
        public static function load(string $filename): Spreadsheet
        {
            return new Spreadsheet();
        }
    }
}
