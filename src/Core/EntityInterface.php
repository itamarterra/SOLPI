<?php

declare(strict_types=1);

namespace SOLPI\Core;

interface EntityInterface
{
    public function id(): ?int;

    public function uuid(): string;

    public function active(): bool;

    public function activate(): static;

    public function deactivate(): static;

    public function metadata(): array;

    public function toArray(): array;
}
