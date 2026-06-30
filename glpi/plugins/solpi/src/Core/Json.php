<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Json
{
    public static function encode(
        mixed $data
    ): string {

        return json_encode(
            $data,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

    }

    public static function decode(
        string $json
    ): array {

        return json_decode(
            $json,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    }
}
