<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Assets\Entities\Asset;

final class AssetMatcher
{
    public function equals(
        Asset $a,
        Asset $b
    ): bool {

        if (
            $a->serial() !== null &&
            $b->serial() !== null &&
            strtoupper($a->serial()) === strtoupper($b->serial())
        ) {
            return true;
        }

        if (
            strtoupper($a->name()) === strtoupper($b->name()) &&
            strtoupper($a->type()) === strtoupper($b->type())
        ) {
            return true;
        }

        return false;
    }

    public function similarity(
        Asset $a,
        Asset $b
    ): float {

        similar_text(
            strtoupper($a->name()),
            strtoupper($b->name()),
            $percent
        );

        return round($percent, 2);
    }
}
