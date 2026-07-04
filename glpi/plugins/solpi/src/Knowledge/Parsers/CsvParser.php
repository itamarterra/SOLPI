<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use RuntimeException;

final class CsvParser
{
    /**
     * Analisa um arquivo CSV e retorna um array de dados.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $data = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",");
            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                if ($headers) {
                    // Garante que o número de colunas corresponde ao de cabeçalhos
                    if (count($headers) === count($row)) {
                        $data[] = array_combine($headers, $row);
                    }
                } else {
                    $data[] = $row;
                }
            }
            fclose($handle);
        }

        return $data;
    }
}

