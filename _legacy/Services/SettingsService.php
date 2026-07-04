<?php

declare(strict_types=1);

namespace SOLPI\Services;

use SOLPI\Models\SettingsModel;
use SOLPI\Repositories\SettingsRepository;

final class SettingsService
{
    private SettingsRepository $repository;

    public function __construct()
    {
        $this->repository = new SettingsRepository();
    }

    public function load(): SettingsModel
    {
        $model = new SettingsModel();

        foreach ($this->repository->load() as $key => $value) {
            $model->set($key, $value);
        }

        return $model;
    }
}
