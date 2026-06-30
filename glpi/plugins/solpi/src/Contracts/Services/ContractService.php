<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Services;

final class ContractService
{
    public function __call(string $method, array $arguments): mixed
    {
        return null;
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}

