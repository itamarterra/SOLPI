<?php
declare(strict_types=1);

namespace SOLPI\Helpers;

final class DateHelper
{
    public static function format(string $date, string $format = 'd/m/Y H:i'): string
    {
        return date($format, strtotime($date));
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

