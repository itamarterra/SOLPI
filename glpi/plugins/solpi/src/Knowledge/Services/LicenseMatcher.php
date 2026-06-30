<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Licenses\Entities\License;

final class LicenseMatcher
{
    public function equals(
        License $a,
        License $b
    ): bool {

        return
            strtoupper($a->serial()) === strtoupper($b->serial());

    }

    public function similarity(
        License $a,
        License $b
    ): float {

        similar_text(
            strtoupper($a->name()),
            strtoupper($b->name()),
            $percent
        );

        return round($percent,2);
    }
}
