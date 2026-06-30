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
<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

use RuntimeException;

final class HttpException extends RuntimeException
{
    private int $statusCode;

    private array $response;

    public function __construct(
        string $message,
        int $statusCode = 0,
        array $response = []
    ) {

        parent::__construct(

            $message,

            $statusCode

        );

        $this->statusCode = $statusCode;

        $this->response = $response;

    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function response(): array
    {
        return $this->response;
    }
}
<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpRequest
{
    private string $method;

    private string $url;

    private array $headers = [];

    private array $query = [];

    private array $body = [];

    private array $files = [];

    private int $timeout = 60;

    public function __construct(
        string $method,
        string $url
    ) {
        $this->method = HttpMethod::normalize($method);

        $this->url = $url;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function addHeader(
        string $name,
        string $value
    ): self {

        $this->headers[$name] = $value;

        return $this;

    }

    public function addQuery(
        string $name,
        mixed $value
    ): self {

        $this->query[$name] = $value;

        return $this;

    }

    public function addBody(
        string $name,
        mixed $value
    ): self {

        $this->body[$name] = $value;

        return $this;

    }

    public function addFile(
        string $name,
        string $path
    ): self {

        $this->files[$name] = $path;

        return $this;

    }

    public function setTimeout(
        int $seconds
    ): self {

        $this->timeout = $seconds;

        return $this;

    }
}
<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpResponse
{
    public function __construct(

        private readonly int $status,

        private readonly array $headers,

        private readonly string $body

    ) {
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function json(): array
    {
        if ($this->body === '') {

            return [];

        }

        $json = json_decode(

            $this->body,

            true

        );

        return is_array($json)

            ? $json

            : [];

    }

    public function successful(): bool
    {
        return HttpStatus::successful(

            $this->status

        );
    }

    public function clientError(): bool
    {
        return HttpStatus::clientError(

            $this->status

        );
    }

    public function serverError(): bool
    {
        return HttpStatus::serverError(

            $this->status

        );
    }
}
