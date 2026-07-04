<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Version
{
    public const DATABASE = '1.0.0';

    public const PLUGIN = '1.0.0';

    public static function database(): string
    {
        return self::DATABASE;
    }

    public static function plugin(): string
    {
        return self::PLUGIN;
    }
}
