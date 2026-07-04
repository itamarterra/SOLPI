<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Parsers;

use SimpleXMLElement;
use RuntimeException;
use Exception;

final class XmlParser
{
    /**
     * Analisa um arquivo XML e retorna um array de dados.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        try {
            $xml = new SimpleXMLElement($filePath, 0, true);
            $json = json_encode($xml);
            if ($json === false) {
                 throw new RuntimeException("Erro ao converter XML para JSON.");
            }
            return json_decode($json, true);
        } catch (Exception $e) {
            throw new RuntimeException("Erro ao processar XML: " . $e->getMessage());
        }
    }
}

