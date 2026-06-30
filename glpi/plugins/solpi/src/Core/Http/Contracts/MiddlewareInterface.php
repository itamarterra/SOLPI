<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Contracts;

interface MiddlewareInterface
{
    public function __invoke(mixed $input = null): mixed;
}

