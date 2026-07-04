<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use DOMDocument;
use DOMXPath;

final class HtmlImporter
{
    public function import(
        string $html
    ): string {

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        $text = '';

        foreach (

            $xpath->query('//body//*[not(self::script)]')

            as $node

        ) {

            $value = trim($node->textContent);

            if ($value === '') {
                continue;
            }

            $text .= $value . PHP_EOL;

        }

        return $text;
    }
}