<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class UserExtractor
{
    public function extract(
        string $text
    ): ?string {

        if (

            preg_match(

                '/([A-ZÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇ]{3,})\s+(TRELICAMP|ECIN|OCTIO)/iu',

                $text,

                $match

            )

        ) {

            return strtoupper(
                trim($match[1])
            );

        }

        return null;
    }
}