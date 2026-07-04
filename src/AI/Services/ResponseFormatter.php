<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class ResponseFormatter
{
    public function format(
        mixed $response
    ): string {

        if(is_string($response))
            return $response;

        $json = json_encode(
            $response,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        );

        return $json === false ? '' : $json;

    }
}
