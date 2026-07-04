<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Extractors;

final class ModelExtractor
{
    public function extract(
        string $text
    ): ?string {

        if (

            preg_match(

                '/(LATITUDE|OPTIPLEX|VOSTRO|INSPIRON|POWEREDGE|PRODESK|ELITEDESK|THINKCENTRE|THINKPAD|ARCHER|RB750)[ A-Z0-9\-]*/i',

                $text,

                $match

            )

        ) {

            return trim($match[0]);

        }

        return null;
    }
}