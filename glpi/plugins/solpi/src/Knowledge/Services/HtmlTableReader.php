<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use DOMDocument;
use DOMXPath;

final class HtmlTableReader
{
    public function read(
        string $html
    ): array {

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        $tables = [];

        foreach ($xpath->query('//table') as $table) {

            $rows = [];

            foreach ($xpath->query('.//tr', $table) as $tr) {

                $cols = [];

                foreach ($xpath->query('./th|./td', $tr) as $td) {

                    $cols[] = trim($td->textContent);

                }

                if (!empty($cols)) {
                    $rows[] = $cols;
                }

            }

            if (!empty($rows)) {
                $tables[] = $rows;
            }

        }

        return $tables;

    }
}
