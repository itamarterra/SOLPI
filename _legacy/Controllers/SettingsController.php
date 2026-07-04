<?php

declare(strict_types=1);

namespace SOLPI\Controllers;

use SOLPI\Services\SettingsService;

final class SettingsController
{
    public function index(): array
    {
        return (new SettingsService())
            ->load()
            ->all();
    }
}
