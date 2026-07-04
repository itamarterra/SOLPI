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

