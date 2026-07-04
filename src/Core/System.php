<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class System
{
    public static function phpVersion(): string
    {
        return PHP_VERSION;
    }

    public static function operatingSystem(): string
    {
        return PHP_OS_FAMILY;
    }

    public static function memoryLimit(): string
    {
        return ini_get('memory_limit');
    }

    public static function uploadLimit(): string
    {
        return ini_get('upload_max_filesize');
    }

    public static function timezone(): string
    {
        return date_default_timezone_get();
    }
}
