<?php
declare(strict_types=1);

namespace SOLPI\Modules\Settings;

final class SettingsController
{
    private SettingsService $service;

    public function __construct()
    {
        $this->service = new SettingsService();
    }

    /**
     * @return array<string,mixed>
     */
    public function list(string $module = 'core'): array
    {
        return [
            'module' => $module,
            'items' => $this->service->all($module),
        ];
    }

    public function set(string $module, string $key, mixed $value, string $type = 'string'): array
    {
        $this->service->set($module, $key, $value, $type);

        return [
            'status' => 'saved',
            'module' => $module,
            'key' => $key,
            'value' => $this->service->get($module, $key),
        ];
    }
}

