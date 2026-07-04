<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use RuntimeException;
use SimpleXMLElement;

final class XmlImporter implements ImporterInterface
{
    public function import(
        string $content
    ): SimpleXMLElement {

        $xml = simplexml_load_string(
            $content
        );

        if ($xml === false) {

            throw new RuntimeException(
                'XML inválido.'
            );

        }

        return $xml;
    }
}