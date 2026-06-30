<?php

declare(strict_types=1);

namespace SOLPI\Core\Http\Auth;

final class BasicAuthentication
{
    public function __construct(

        private readonly string $username,

        private readonly string $password

    ) {
    }

    public function header(): array
    {
        return [

            'Authorization' =>

                'Basic ' .

                base64_encode(

                    $this->username .

                    ':' .

                    $this->password

                )

        ];
    }
}