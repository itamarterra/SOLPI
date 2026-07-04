<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class ModelDetector
{
    public function detect(
        string $description
    ): ?string {

        if(

            preg_match(

                '/(LATITUDE|OPTIPLEX|VOSTRO|INSPIRON|POWEREDGE|PRODESK|THINKPAD|THINKCENTRE|ELITEDESK|ARCHER|RB750)[ A-Z0-9\-]*/i',

                $description,

                $match

            )

        ){

            return trim($match[0]);

        }

        return null;

    }
}
