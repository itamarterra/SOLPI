<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Request
{
    public function input(
        string $key,
        mixed $default=null
    ): mixed{

        return $_POST[$key]

            ?? $_GET[$key]

            ?? $default;

    }

    public function all(): array
    {
        return array_merge(

            $_GET,

            $_POST

        );
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return strtok(

            $_SERVER['REQUEST_URI'],

            '?'

        );
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}
