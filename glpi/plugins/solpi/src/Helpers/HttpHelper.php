<?php
declare(strict_types=1);

namespace SOLPI\Helpers;

final class HttpHelper
{
    public static function get(string $url, array $headers = []): array
    {
        $builder = new \SOLPI\Core\Http\Transport\CurlRequestBuilder();
        $ch = $builder->setUrl($url)
            ->setMethod('GET')
            ->setHeaders($headers)
            ->build();
        
        $response = curl_exec($ch);
        $parser = new \SOLPI\Core\Http\Transport\CurlResponseParser();
        $result = $parser->parse((string)$response);
        curl_close($ch);
        
        return $result;
    }

    public static function post(string $url, array $payload, array $headers = []): array
    {
        $builder = new \SOLPI\Core\Http\Transport\CurlRequestBuilder();
        $ch = $builder->setUrl($url)
            ->setMethod('POST')
            ->setHeaders($headers)
            ->setPayload(json_encode($payload))
            ->build();
        
        $response = curl_exec($ch);
        $parser = new \SOLPI\Core\Http\Transport\CurlResponseParser();
        $result = $parser->parse((string)$response);
        curl_close($ch);
        
        return $result;
    }
}

