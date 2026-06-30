<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Extractors;

use DateTime;

final class WarrantyExtractor
{
    public function extract(
        string $text
    ): ?DateTime {

        if (

            preg_match(

                '/(\d{2}\/\d{2}\/\d{4})/',

                $text,

                $match

            )

        ) {

            return DateTime::createFromFormat(
                'd/m/Y',
                $match[1]
            );

        }

        return null;
    }
}