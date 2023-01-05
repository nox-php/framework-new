<?php

namespace Nox\Framework\Installer\Providers;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Installer\Console\Commands\InstallNoxCommand;
use Nox\Framework\Installer\Console\Commands\SeedNoxDefaults;

class InstallerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedNoxDefaults::class,
                InstallNoxCommand::class,
            ]);
        }
    }
}
