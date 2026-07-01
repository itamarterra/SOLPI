<?php

declare(strict_types=1);

namespace SOLPI\Contracts;

interface HttpClientInterface
{
    /**
     * @param string $url
     * @param array<string,string> $headers
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function get(
        string $url,
        array $headers = [],
        array $query = []
    ): array;

    /**
     * @param string $url
     * @param array<string,string> $headers
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    public function post(
        string $url,
        array $headers = [],
        array $body = []
    ): array;

    /**
     * @param string $url
     * @param array<string,string> $headers
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    public function put(
        string $url,
        array $headers = [],
        array $body = []
    ): array;

    /**
     * @param string $url
     * @param array<string,string> $headers
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    public function delete(
        string $url,
        array $headers = [],
        array $body = []
    ): array;
}