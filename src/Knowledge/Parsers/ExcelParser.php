<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Lê arquivos Excel (.xlsx, .xls) com detecção inteligente de cabeçalho.
 */
final class ExcelParser
{
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

        // --- Detecção Inteligente de Cabeçalho ---
        // Pula linhas iniciais decorativas até encontrar a linha que parece ser o cabeçalho real
        $header = [];
        $headerIndex = -1;

        foreach ($rows as $index => $rowData) {
            $nonEmptyCount = count(array_filter($rowData));
            // Se a linha tem mais de 3 colunas preenchidas, provavelmente é o cabeçalho
            if ($nonEmptyCount >= 3) {
                $header = array_map(
                    static fn($v): string => trim((string)($v ?? '')),
                    $rowData
                );
                $headerIndex = $index;
                break;
            }
        }

        if (empty($header)) {
            return [];
        }

        $result = [];
        foreach ($rows as $index => $rowData) {
            // Pula as linhas anteriores ao cabeçalho e a linha do próprio cabeçalho
            if ($index <= $headerIndex) {
                continue;
            }

            $row = [];
            foreach ($header as $col => $colName) {
                if ($colName !== '') {
                    $row[$colName] = trim((string)($rowData[$col] ?? ''));
                }
            }

            // Ignora linhas completamente vazias
            if (count(array_filter($row)) > 0) {
                $result[] = $row;
            }
        }

        return $result;
    }

    public function getHeaders(string $filePath): array
    {
        $rows = $this->parse($filePath);
        if (empty($rows)) return [];
        return array_keys($rows[0]);
    }
}
