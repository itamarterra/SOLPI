<?php

declare(strict_types=1);

namespace SOLPI\Core;

use Ramsey\Uuid\Uuid as RamseyUuid;

final class Uuid
{
    public static function generate(): string
    {
        return RamseyUuid::uuid4()->toString();
    }

    public static function valid(
        string $uuid
    ): bool {

        return RamseyUuid::isValid(
            $uuid
        );

    }
}
