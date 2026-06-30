<?php

declare(strict_types=1);

namespace SOLPI\Core\Http\Auth;

final class BearerAuthentication
{
    public function __construct(

        private readonly string $token

    ) {
    }

    public function token(): string
    {
        return $this->token;
    }

    public function header(): array
    {
        return [

            'Authorization' =>

                'Bearer ' . $this->token

        ];
    }
}