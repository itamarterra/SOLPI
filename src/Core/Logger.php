<?php

declare(strict_types=1);

namespace SOLPI\Core;

use DateTime;

final class Logger
{
    private string $directory;

    public function initialize(
        ?string $directory = null
    ): void {

        $this->directory = $directory
            ?? dirname(__DIR__, 2) . '/logs';

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->write('DEBUG', $message, $context);
    }

    private function write(
        string $level,
        string $message,
        array $context
    ): void {

        $date = new DateTime();

        $file = sprintf(
            '%s/%s.log',
            $this->directory,
            $date->format('Y-m-d')
        );

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            $date->format('Y-m-d H:i:s'),
            $level,
            $message,
            empty($context)
                ? ''
                : json_encode(
                    $context,
                    JSON_UNESCAPED_UNICODE
                ),
            PHP_EOL
        );

        file_put_contents(
            $file,
            $line,
            FILE_APPEND | LOCK_EX
        );
    }
}