<?php

namespace Nox\Framework\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Settings\Facades\Settings;
use Nox\Framework\Settings\SettingsManager;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('settings', SettingsManager::class);
    }

    public function boot(): void
    {
        $this->app->terminating(static function () {
            Settings::save();
        });
    }

    public function provides(): array
    {
        return [
            'settings',
            SettingsManager::class,
        ];
    }
}
