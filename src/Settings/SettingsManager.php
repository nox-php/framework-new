<?php

namespace Nox\Framework\Settings;

use Illuminate\Support\Manager;
use Nox\Framework\Settings\Drivers\SettingsFileDriver;

class SettingsManager extends Manager
{
    public function getDefaultDriver()
    {
        return config('nox.settings.default');
    }

    public function createFileDriver(): SettingsFileDriver
    {
        return app(SettingsFileDriver::class);
    }
}
