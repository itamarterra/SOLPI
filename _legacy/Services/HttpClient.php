<?php

declare(strict_types=1);

namespace SOLPI\Services;

use RuntimeException;
use SOLPI\Contracts\HttpClientInterface;

final class HttpClient implements HttpClientInterface
{
    public function get(
        string $url,
        array $headers = [],
        array $query = []
    ): array {

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request('GET', $url, $headers);
    }

    public function post(
        string $url,
        array $headers = [],
        array $body = []
    ): array {

        return $this->request(
            'POST',
            $url,
            $headers,
            $body
        );
    }

    public function put(
        string $url,
        array $headers = [],
        array $body = []
    ): array {

        return $this->request(
            'PUT',
            $url,
            $headers,
            $body
        );
    }

    public function delete(
        string $url,
        array $headers = [],
        array $body = []
    ): array {

        return $this->request(
            'DELETE',
            $url,
            $headers,
            $body
        );
    }

    private function request(
        string $method,
        string $url,
        array $headers = [],
        array $body = []
    ): array {

        $curl = curl_init();

        $httpHeaders = [];

        foreach ($headers as $key => $value) {
            $httpHeaders[] = "{$key}: {$value}";
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (!empty($body)) {

            curl_setopt(
                $curl,
                CURLOPT_POSTFIELDS,
                json_encode($body, JSON_UNESCAPED_UNICODE)
            );

        }

        $response = curl_exec($curl);

        if ($response === false) {
            throw new RuntimeException(curl_error($curl));
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            'status' => $status,
            'body' => json_decode($response, true)
        ];
    }
}