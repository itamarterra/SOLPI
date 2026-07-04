<?php

declare(strict_types=1);

namespace SOLPI\Core\Logging;

use DateTimeImmutable;
use JsonException;

final class LogFormatter
{
    public function format(
        string $level,
        string $message,
        array $context = []
    ): string {

        $record = [

            'timestamp' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),

            'level' => strtoupper($level),

            'message' => $message,

            'context' => $context

        ];

        try {

            return json_encode(

                $record,

                JSON_THROW_ON_ERROR |
                JSON_UNESCAPED_UNICODE

            ) . PHP_EOL;

        } catch (JsonException) {

            return sprintf(

                "[%s] [%s] %s%s",

                $record['timestamp'],

                $record['level'],

                $message,

                PHP_EOL

            );

        }

    }
}