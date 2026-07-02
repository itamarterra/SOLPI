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

        if (method_exists(RamseyUuid::class, 'isValid')) {
            return RamseyUuid::isValid(
                $uuid
            );
        }

        return (bool)preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $uuid
        );

    }
}
