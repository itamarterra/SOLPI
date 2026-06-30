<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

final class ArrayHelper
{
    private function __construct()
    {
    }

    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        return $array[$key] ?? $default;
    }

    public static function has(array $array, string $key): bool
    {
        return array_key_exists($key, $array);
    }

    public static function remove(array &$array, string $key): void
    {
        unset($array[$key]);
    }
}