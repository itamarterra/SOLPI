<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use ZipArchive;
use RuntimeException;

final class DocxParser
{
    public function parse(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Não foi possível abrir o arquivo DOCX.");
        }

        $content = '';
        $documentXml = 'word/document.xml';

        if (($index = $zip->locateName($documentXml)) !== false) {
            $data = $zip->getFromIndex($index);
            // Remove tags XML para extrair apenas o texto
            $content = strip_tags($data);
        }

        $zip->close();

        return trim($content);
    }
}

