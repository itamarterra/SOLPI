<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Cache
{
    private static array $cache=[];

    public static function put(
        string $key,
        mixed $value
    ): void {

        self::$cache[$key]=$value;

    }

    public static function get(
        string $key,
        mixed $default=null
    ): mixed {

        return self::$cache[$key]

            ?? $default;

    }

    public static function has(
        string $key
    ): bool {

        return isset(

            self::$cache[$key]

        );

    }

    public static function forget(
        string $key
    ): void {

        unset(

            self::$cache[$key]

        );

    }

    public static function clear(): void
    {
        self::$cache=[];
    }
}
