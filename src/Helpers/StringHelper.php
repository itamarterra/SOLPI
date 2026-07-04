<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

final class StringHelper
{
    private function __construct()
    {
    }

    public static function startsWith(string $text, string $search): bool
    {
        return str_starts_with($text, $search);
    }

    public static function endsWith(string $text, string $search): bool
    {
        return str_ends_with($text, $search);
    }

    public static function contains(string $text, string $search): bool
    {
        return str_contains($text, $search);
    }

    public static function slug(string $text): string
    {
        $text = strtolower($text);

        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);

        return trim((string) $text, '-');
    }
}