<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Environment
{
    public static function get(
        string $key,
        mixed $default=null
    ): mixed {

        $envValue = getenv($key);

        return $_ENV[$key]

            ?? ($envValue !== false ? $envValue : null)

            ?? $default;

    }

    public static function has(
        string $key
    ): bool {

        return self::get($key)!==null;

    }

    public static function set(
        string $key,
        mixed $value
    ): void {

        $_ENV[$key]=$value;

    }
}
