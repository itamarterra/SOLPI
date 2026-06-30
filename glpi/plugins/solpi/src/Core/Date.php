<?php

declare(strict_types=1);

namespace SOLPI\Core;

use DateTime;

final class Date
{
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function today(): string
    {
        return date('Y-m-d');
    }

    public static function create(
        string $date
    ): DateTime {

        return new DateTime($date);

    }

    public static function format(
        DateTime $date
    ): string {

        return $date->format(
            'Y-m-d H:i:s'
        );

    }
}
