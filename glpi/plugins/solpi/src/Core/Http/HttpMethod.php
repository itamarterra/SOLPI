<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpMethod
{
    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const PATCH = 'PATCH';

    public const DELETE = 'DELETE';

    public const HEAD = 'HEAD';

    public const OPTIONS = 'OPTIONS';

    public const TRACE = 'TRACE';

    public const CONNECT = 'CONNECT';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [

            self::GET,

            self::POST,

            self::PUT,

            self::PATCH,

            self::DELETE,

            self::HEAD,

            self::OPTIONS,

            self::TRACE,

            self::CONNECT

        ];
    }

    public static function exists(
        string $method
    ): bool {

        return in_array(

            strtoupper($method),

            self::all(),

            true

        );

    }

    public static function normalize(
        string $method
    ): string {

        return strtoupper(

            trim($method)

        );

    }
}

