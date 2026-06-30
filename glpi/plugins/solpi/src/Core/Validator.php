<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Validator
{
    private array $errors=[];

    public function required(
        string $field,
        mixed $value
    ): self {

        if(

            $value===null ||

            $value===''

        ){

            $this->errors[]="$field é obrigatório.";

        }

        return $this;

    }

    public function email(
        string $field,
        ?string $value
    ): self{

        if(

            $value!==null &&

            !filter_var(

                $value,

                FILTER_VALIDATE_EMAIL

            )

        ){

            $this->errors[]="$field inválido.";

        }

        return $this;

    }

    public function valid(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
