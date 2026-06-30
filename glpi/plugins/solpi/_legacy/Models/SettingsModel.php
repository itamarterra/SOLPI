<?php

declare(strict_types=1);

namespace SOLPI\Models;

final class SettingsModel
{
    private array $settings = [];

    public function set(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->settings;
    }
}
