<?php

declare(strict_types=1);

namespace SOLPI\Tools\Auditor;

final class AuditReport
{
    private array $data = [];

    public function set(
        string $key,
        mixed $value
    ): self {

        $this->data[$key] = $value;

        return $this;

    }

    public function add(
        string $key,
        mixed $value
    ): self {

        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }

        $this->data[$key][] = $value;

        return $this;

    }

    public function increment(
        string $key,
        int $value = 1
    ): self {

        if (!isset($this->data[$key])) {
            $this->data[$key] = 0;
        }

        $this->data[$key] += $value;

        return $this;

    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {

        return $this->data[$key] ?? $default;

    }

    public function has(
        string $key
    ): bool {

        return array_key_exists(
            $key,
            $this->data
        );

    }

    public function all(): array
    {
        return $this->data;
    }

    public function clear(): self
    {
        $this->data = [];

        return $this;
    }
}