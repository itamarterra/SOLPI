<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpStatus
{
    public const OK = 200;

    public const CREATED = 201;

    public const ACCEPTED = 202;

    public const NO_CONTENT = 204;

    public const MOVED_PERMANENTLY = 301;

    public const FOUND = 302;

    public const NOT_MODIFIED = 304;

    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const METHOD_NOT_ALLOWED = 405;

    public const CONFLICT = 409;

    public const UNPROCESSABLE_ENTITY = 422;

    public const TOO_MANY_REQUESTS = 429;

    public const INTERNAL_SERVER_ERROR = 500;

    public const BAD_GATEWAY = 502;

    public const SERVICE_UNAVAILABLE = 503;

    public const GATEWAY_TIMEOUT = 504;

    private function __construct()
    {
    }

    public static function successful(
        int $status
    ): bool {

        return $status >= 200 && $status < 300;

    }

    public static function clientError(
        int $status
    ): bool {

        return $status >= 400 && $status < 500;

    }

    public static function serverError(
        int $status
    ): bool {

        return $status >= 500;

    }
}

