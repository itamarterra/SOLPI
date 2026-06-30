<?php
declare(strict_types=1);

namespace SOLPI\Core\Http;

final class HttpResponse
{
    public function __call(string $method, array $arguments): mixed
    {
        return null;
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}

