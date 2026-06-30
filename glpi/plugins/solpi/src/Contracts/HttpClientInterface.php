<?php

declare(strict_types=1);

namespace SOLPI\Contracts;

interface HttpClientInterface
{
    public function get(
        string $url,
        array $headers = [],
        array $query = []
    ): array;

    public function post(
        string $url,
        array $headers = [],
        array $body = []
    ): array;

    public function put(
        string $url,
        array $headers = [],
        array $body = []
    ): array;

    public function delete(
        string $url,
        array $headers = [],
        array $body = []
    ): array;
}