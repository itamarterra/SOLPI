<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use RuntimeException;

final class JsonParser
{
    /**
     * Analisa um arquivo JSON e retorna um array de dados.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Não foi possível ler o arquivo JSON.");
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Erro ao decodificar JSON: " . json_last_error_msg());
        }

        return $data;
    }
}

