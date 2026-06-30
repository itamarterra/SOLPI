<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

/**
 * Lê arquivos Excel (.xlsx, .xls) e retorna os dados como array.
 *
 * Detecta automaticamente o cabeçalho na primeira linha e
 * retorna cada linha subsequente como array associativo.
 */
final class ExcelParser
{
    /**
     * Lê um arquivo Excel e retorna array de linhas.
     *
     * @param  string $filePath Caminho absoluto para o arquivo .xlsx/.xls
     * @return array<int, array<string, string>>  Linhas com chave = nome da coluna
     * @throws RuntimeException
     */
    public function parse(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return [];
        }

        // Primeira linha = cabeçalho
        $header = array_map(
            static fn($v): string => trim((string)($v ?? '')),
            array_shift($rows)
        );

        $result = [];

        foreach ($rows as $rowData) {
            $row = [];
            foreach ($header as $col => $colName) {
                if ($colName !== '') {
                    $row[$colName] = trim((string)($rowData[$col] ?? ''));
                }
            }
            // Ignora linhas completamente vazias
            if (array_filter($row) !== []) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Retorna apenas o cabeçalho (nomes das colunas) sem os dados.
     */
    public function getHeaders(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $firstRow    = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, true, true, false)[0] ?? [];

        return array_values(array_filter(
            array_map(static fn($v): string => trim((string)($v ?? '')), $firstRow)
        ));
    }
}