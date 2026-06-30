<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Paths
{
    public static function root(): string
    {
        return dirname(__DIR__,2);
    }

    public static function logs(): string
    {
        return self::root().'/storage/logs';
    }

    public static function cache(): string
    {
        return self::root().'/storage/cache';
    }

    public static function tmp(): string
    {
        return self::root().'/storage/tmp';
    }
}
