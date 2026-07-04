<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use RuntimeException;

final class ImportFactory
{
    public static function make(
        string $type
    ): ImporterInterface {

        return match (strtolower($type)) {

            'html' => new HtmlImporter(),

            'csv' => new CsvImporter(),

            'txt' => new TxtImporter(),

            'json' => new JsonImporter(),

            'xml' => new XmlImporter(),

            default => throw new RuntimeException(
                "Importer '{$type}' não suportado."
            )

        };
    }
}