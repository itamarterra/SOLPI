<?php

declare(strict_types=1);

namespace SOLPI\Core;

use RuntimeException;

final class Config
{
    /**
     * @var array<string,mixed>
     */
    private array $items = [];

    private bool $loaded = false;

    public function load(?string $directory = null): void
    {
        if ($this->loaded) {
            return;
        }

        $directory ??= dirname(__DIR__, 2) . '/config';

        if (!is_dir($directory)) {
            $this->loaded = true;
            return;
        }

        $files = glob($directory . '/*.php');

        foreach ($files as $file) {

            $config = require $file;

            if (!is_array($config)) {
                continue;
            }

            $name = basename($file, '.php');

            $this->items[$name] = $config;
        }

        $this->loaded = true;
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {

        $segments = explode('.', $key);

        $value = $this->items;

        foreach ($segments as $segment) {

            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(
        string $key,
        mixed $value
    ): void {

        $segments = explode('.', $key);

        $config = &$this->items;

        foreach ($segments as $segment) {

            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }

            $config = &$config[$segment];
        }

        $config = $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key, '__NULL__') !== '__NULL__';
    }

    public function all(): array
    {
        return $this->items;
    }

    public function require(string $key): mixed
    {
        if (!$this->has($key)) {

            throw new RuntimeException(
                "Configuração '{$key}' não encontrada."
            );

        }

        return $this->get($key);
    }
}