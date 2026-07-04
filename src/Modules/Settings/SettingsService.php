<?php
declare(strict_types=1);

namespace SOLPI\Modules\Settings;

use SOLPI\Core\Config;

final class SettingsService
{
    private SettingsRepository $repository;

    public function __construct()
    {
        $this->repository = new SettingsRepository();
    }

    /**
     * @return array<string,mixed>
     */
    public function all(string $module = 'core'): array
    {
        return $this->repository->all($module);
    }

    public function get(string $module, string $key, mixed $default = null): mixed
    {
        $value = $this->repository->get($module, $key, '__missing__');
        if ($value !== '__missing__') {
            return $value;
        }

        $config = new Config();
        $config->load();

        return $config->get($module . '.' . $key, $default);
    }

    public function set(string $module, string $key, mixed $value, string $type = 'string'): void
    {
        $this->repository->upsert($module, $key, $value, $type);
    }
}

