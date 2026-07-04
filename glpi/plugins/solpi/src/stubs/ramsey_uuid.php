<?php

declare(strict_types=1);

namespace Ramsey\Uuid;

/**
 * Minimal stub for Ramsey\Uuid\Uuid used by the project for static analysis.
 * This is ONLY for static analysis and local tooling; do not rely on it at runtime.
 */
if (class_exists(Uuid::class, false)) {
    return;
}

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

    public static function isValid(string $uuid): bool
    {
        return (bool)preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $uuid
        );
    }
}
