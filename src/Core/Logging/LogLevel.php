<?php

declare(strict_types=1);

namespace SOLPI\Core\Logging;

final class LogLevel
{
    public const EMERGENCY = 'emergency';

    public const ALERT = 'alert';

    public const CRITICAL = 'critical';

    public const ERROR = 'error';

    public const WARNING = 'warning';

    public const NOTICE = 'notice';

    public const INFO = 'info';

    public const DEBUG = 'debug';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [

            self::EMERGENCY,

            self::ALERT,

            self::CRITICAL,

            self::ERROR,

            self::WARNING,

            self::NOTICE,

            self::INFO,

            self::DEBUG

        ];
    }

    public static function exists(
        string $level
    ): bool {

        return in_array(

            strtolower($level),

            self::all(),

            true

        );

    }
}