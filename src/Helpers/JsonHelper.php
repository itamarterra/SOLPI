<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

use JsonException;

final class JsonHelper
{
    private function __construct()
    {
    }

    /**
     * @throws JsonException
     */
    public static function encode(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
        );
    }

    /**
     * @throws JsonException
     */
    public static function decode(string $json): mixed
    {
        return json_decode(
            $json,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}