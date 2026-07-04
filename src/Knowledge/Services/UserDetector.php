<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class UserDetector
{
    public function detect(
        string $description
    ): ?string {

        $words = preg_split(
            '/\s+/',
            strtoupper($description)
        );

        if (!$words) {
            return null;
        }

        $ignored = [

            'DELL','HP','LENOVO','NOTEBOOK','DESKTOP',

            'MONITOR','IMPRESSORA','SERVER',

            'SERVIDOR','OPTIPLEX','LATITUDE',

            'INSPIRON','VOSTRO','MICRO',

            'TRELICAMP'

        ];

        foreach ($words as $word) {

            if (strlen($word) < 3) {
                continue;
            }

            if (in_array($word,$ignored,true)) {
                continue;
            }

            return $word;

        }

        return null;

    }
}
