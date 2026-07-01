<?php

declare(strict_types=1);

namespace Ramsey\Uuid;

/**
 * Minimal stub for Ramsey\Uuid\Uuid used by the project for static analysis.
 * This is ONLY for static analysis and local tooling; do not rely on it at runtime.
 */
class Uuid
{
    /**
     * @return self
     */
    public static function uuid4(): self
    {
        return new self();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return '';
    }
}
