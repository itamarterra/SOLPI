<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use DOMDocument;
use RuntimeException;

final class HtmlParser
{
    /**
     * Analisa um arquivo HTML e extrai o texto visível.
     */
    public function parse(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Não foi possível ler o arquivo HTML.");
        }

        $dom = new DOMDocument();
        // Silencia avisos de HTML mal formatado
        @$dom->loadHTML($content);
        
        return trim($dom->textContent);
    }
}

