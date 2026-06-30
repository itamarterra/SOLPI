<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use DateTime;

final class WarrantyDetector
{
    public function detect(
        string $text
    ): ?DateTime {

        if(

            preg_match(

                '/(\d{2}\/\d{2}\/\d{4})/',

                $text,

                $match

            )

        ){

            return DateTime::createFromFormat(

                'd/m/Y',

                $match[1]

            );

        }

        return null;

    }
}
