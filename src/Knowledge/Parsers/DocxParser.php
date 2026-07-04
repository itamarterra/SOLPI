<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use ZipArchive;
use RuntimeException;
use DOMDocument;

/**
 * Extrai texto de arquivos .docx (XML compactado)
 */
final class DocxParser
{
    public function parse(string $filePath): string
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("Arquivo não legível: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return "";
        }

        // No DOCX, o conteúdo principal fica em word/document.xml
        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xmlContent) {
            return "";
        }

        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);

        // Retorna o texto plano removendo as tags XML
        return strip_tags($dom->saveXML());
    }
}
