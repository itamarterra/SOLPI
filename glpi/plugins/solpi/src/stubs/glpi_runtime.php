<?php

declare(strict_types=1);

if (!function_exists('__')) {
    function __(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

if (!class_exists('CommonGLPI')) {
    class CommonGLPI
    {
    }
}

if (!class_exists('Session')) {
    class Session
    {
        public static function checkLoginUser(): void
        {
        }

        public static function haveRight(string $right, int $level): bool
        {
            return true;
        }

        public static function getLoginUserID(): int
        {
            return 0;
        }
    }
}

if (!class_exists('Html')) {
    class Html
    {
        public static function header(string $title = '', string $menu = '', string $front = '', string $entity = ''): void
        {
        }

        public static function footer(): void
        {
        }
    }
}

if (!class_exists('Toolbox')) {
    class Toolbox
    {
        public static function logError(string $message): void
        {
        }
    }
}

if (!class_exists('Migration')) {
    class Migration
    {
    }
}
