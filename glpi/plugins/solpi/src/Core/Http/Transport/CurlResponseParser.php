<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Transport;

final class CurlResponseParser
{
    public function parse(string $response): array
    {
        $data = json_decode($response, true);
        return is_array($data) ? $data : ['raw' => $response];
    }

    public function getStatusCode($ch): int
    {
        return (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
}

