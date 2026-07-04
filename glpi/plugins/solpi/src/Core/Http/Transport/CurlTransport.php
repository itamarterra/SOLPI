<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Transport;

use RuntimeException;

final class CurlTransport
{
    public function send(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("Erro na requisição CURL: {$error}");
        }

        return [
            'status' => $statusCode,
            'body'   => $response
        ];
    }
}

