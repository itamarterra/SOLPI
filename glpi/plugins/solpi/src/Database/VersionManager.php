<?php

declare(strict_types=1);

namespace SOLPI\Database;

final class VersionManager
{
    public const VERSION = '2.0.0-alpha';

    public static function current(): string
    {
        return self::VERSION;
    }

    public static function needsUpdate(string $installedVersion): bool
    {
        return version_compare(
            $installedVersion,
            self::VERSION,
            '<'
        );
    }
}