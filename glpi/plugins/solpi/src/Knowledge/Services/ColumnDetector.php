<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class ColumnDetector
{
    public function detect(
        array $header
    ): array {

        $columns = [];

        foreach ($header as $index => $column) {

            $name = strtoupper(trim($column));

            if (str_contains($name,'DESCRI')) {
                $columns['description'] = $index;
            }

            if (str_contains($name,'SERIAL')) {
                $columns['serial'] = $index;
            }

            if (str_contains($name,'TIPO')) {
                $columns['type'] = $index;
            }

            if (str_contains($name,'GARANT')) {
                $columns['warranty'] = $index;
            }

            if (str_contains($name,'VALOR')) {
                $columns['value'] = $index;
            }

            if (str_contains($name,'NF')) {
                $columns['invoice'] = $index;
            }

        }

        return $columns;

    }
}
