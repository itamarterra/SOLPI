<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use RuntimeException;

final class PdfParser
{
    /**
     * Extrai texto de um arquivo PDF.
     * Implementação otimizada para o SOLPI sem dependências externas pesadas.
     */
    public function parse(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Não foi possível ler o arquivo PDF.");
        }

        $text = '';
        
        // Estratégia 1: Extrair blocos de texto PDF padrão (Tj, TJ)
        if (preg_match_all('/(?:\((.*?)\) Tj|\[(.*?)\] TJ)/is', $content, $matches)) {
            foreach ($matches[1] as $match) {
                if ($match !== '') {
                    $text .= $match . ' ';
                }
            }
            foreach ($matches[2] as $match) {
                // Limpeza básica para blocos TJ que podem conter números de espaçamento
                $cleanMatch = preg_replace('/-?\d+/', '', $match);
                $cleanMatch = preg_replace('/[\(\)]/', '', $cleanMatch);
                $text .= $cleanMatch . ' ';
            }
        }

        // Estratégia 2: Fallback se não encontrar blocos padrão
        if (strlen(trim($text)) < 10) {
            $text = preg_replace('/[^a-zA-Z0-9\s\.\,\;\:\-\_\@\/\n]/', '', $content);
            $text = preg_replace('/\s+/', ' ', $text);
        }

        return trim($text);
    }
}

