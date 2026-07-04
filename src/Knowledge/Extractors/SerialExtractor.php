<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Extractors;

final class SerialExtractor
{
    public function extract(
        string $text
    ): ?string {

        if (

            preg_match(

                '/([A-Z0-9]{6,25})/',

                strtoupper($text),

                $match

            )

        ) {

            return $match[1];

        }

        return null;
    }
}