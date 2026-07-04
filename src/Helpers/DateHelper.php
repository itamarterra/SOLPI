<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

use DateTime;
use Exception;

final class DateHelper
{
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function format(?string $date, string $format = 'd/m/Y H:i'): string
    {
        if (empty($date)) {
            return '';
        }
        try {
            $dt = new DateTime($date);
            return $dt->format($format);
        } catch (Exception) {
            return $date;
        }
    }

    public static function diffInMinutes(string $start, string $end): int
    {
        try {
            $s = new DateTime($start);
            $e = new DateTime($end);
            return (int)abs(($e->getTimestamp() - $s->getTimestamp()) / 60);
        } catch (Exception) {
            return 0;
        }
    }
}
