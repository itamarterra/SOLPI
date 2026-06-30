<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Entities;

final class Contract
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

