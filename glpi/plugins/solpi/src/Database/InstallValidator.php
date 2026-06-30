<?php

declare(strict_types=1);

namespace SOLPI\Database;

final class InstallValidator
{
    public function validate(): array
    {
        $errors=[];

        if(version_compare(PHP_VERSION,'8.2','<')){

            $errors[]='PHP 8.2 ou superior é obrigatório.';

        }

        if(!extension_loaded('pdo')){

            $errors[]='Extensão PDO não encontrada.';

        }

        if(!extension_loaded('json')){

            $errors[]='Extensão JSON não encontrada.';

        }

        if(!extension_loaded('mbstring')){

            $errors[]='Extensão MBString não encontrada.';

        }

        return $errors;
    }

    public function passed(): bool
    {
        return empty($this->validate());
    }
}
