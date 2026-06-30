<?php

declare(strict_types=1);

namespace SOLPI\Core\Database\Exceptions;

use RuntimeException;
use Throwable;

final class DatabaseException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}