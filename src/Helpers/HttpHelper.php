<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

final class HttpHelper
{
    /**
     * @param array<string,string> $headers
     * @return array<string,string>
     */
    public static function defaultHeaders(string $token = ''): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'SOLPI-Plugin/2.0'
        ];

        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    public static function isSuccess(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 300;
    }

    public static function parseResponse(string $body): array
    {
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
    }
}
