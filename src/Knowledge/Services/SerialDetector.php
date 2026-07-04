<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class SerialDetector
{
    public function detect(
        string $text
    ): ?string {

        if(

            preg_match(

                '/([A-Z0-9]{6,25})/',

                strtoupper($text),

                $match

            )

        ){

            return $match[1];

        }

        return null;

    }
}
