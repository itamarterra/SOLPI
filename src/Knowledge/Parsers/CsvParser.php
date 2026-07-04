<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use RuntimeException;

/**
 * Lê arquivos CSV e retorna os dados como array associativo.
 */
final class CsvParser
{
    /**
     * @return array<int, array<string, string>>
     */
    public function parse(string $filePath, string $delimiter = ','): array
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("Arquivo não legível: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            return [];
        }

        $result = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = [];
            foreach ($header as $i => $name) {
                $row[trim($name)] = trim($data[$i] ?? '');
            }
            $result[] = $row;
        }

        fclose($handle);
        return $result;
    }
}
