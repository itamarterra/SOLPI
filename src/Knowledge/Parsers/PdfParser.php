<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use RuntimeException;

/**
 * Extrai texto de arquivos PDF.
 * Tenta usar o binário pdftotext do sistema ou retorna aviso de dependência.
 */
final class PdfParser
{
    public function parse(string $filePath): string
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("Arquivo não legível: {$filePath}");
        }

        // Tenta usar pdftotext (comum em servidores Linux/Docker)
        $output = [];
        $returnVar = 0;

        // Executa pdftotext e manda para o stdout (-)
        exec("pdftotext " . escapeshellarg($filePath) . " -", $output, $returnVar);

        if ($returnVar === 0 && !empty($output)) {
            return implode("\n", $output);
        }

        return "ERRO: O extrator de PDF (pdftotext) não está instalado no servidor ou o PDF está protegido.";
    }
}
