<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Response
{
    public static function json(
        mixed $data,
        int $status=200
    ): void{

        http_response_code($status);

        header(
            'Content-Type: application/json'
        );

        echo json_encode(

            $data,

            JSON_PRETTY_PRINT |

            JSON_UNESCAPED_UNICODE

        );

    }

    public static function success(
        string $message,
        array $data=[]
    ): void{

        self::json([

            'success'=>true,

            'message'=>$message,

            'data'=>$data

        ]);

    }

    public static function error(
        string $message,
        int $status=500
    ): void{

        self::json([

            'success'=>false,

            'message'=>$message

        ],$status);

    }
}
