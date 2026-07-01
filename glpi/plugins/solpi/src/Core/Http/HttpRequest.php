<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpRequest
{
    private string $method;

    private string $url;

    /** @var array<string,string> */
    private array $headers = [];

    /** @var array<string,mixed> */
    private array $query = [];

    /** @var array<string,mixed> */
    private array $body = [];

    /** @var array<string,string> */
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

