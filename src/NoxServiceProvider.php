<?php

namespace Nox\Framework;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nox\Framework\Admin\Providers\AdminServiceProvider;
use Nox\Framework\Auth\Providers\AuthServiceProvider;
use Nox\Framework\Extend\Providers\ModuleServiceProvider;
use Nox\Framework\Installer\Providers\InstallerServiceProvider;
use Nox\Framework\Settings\Providers\SettingsServiceProvider;
use Nox\Framework\Theme\Providers\ThemeServiceProvider;
use Nox\Framework\Transformer\Provider\TransformerServiceProvider;
use Nox\Framework\Updater\Jobs\NoxCheckUpdateJob;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use SplFileInfo;

class NoxServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        TransformerServiceProvider::class,
        SettingsServiceProvider::class,
        AuthServiceProvider::class,
        InstallerServiceProvider::class,
        AdminServiceProvider::class,
        ModuleServiceProvider::class,
        ThemeServiceProvider::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nox.php', 'nox');
        $this->mergeConfigFrom(__DIR__.'/../config/localisation.php', 'localisation');

        $this->addMacros();

        parent::register();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nox');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nox');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/nox.php' => config_path('nox.php'),
                __DIR__.'/../config/localisation.php' => config_path('localisation.php.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../dist' => public_path('nox'),
            ], 'assets');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->setupScheduler();
    }

    protected function addMacros(): void
    {
        $this->app->resolving('files', function () {
            File::macro('search', function (string $path, string $name): array {
                if (! File::exists($path)) {
                    return [];
                }

                $name = Str::lower($name);

                return collect(File::allFiles($path))
                    ->filter(static fn (SplFileInfo $file): bool => Str::lower($file->getFileName()) === $name)
                    ->map(static fn (SplFileInfo $file): string => $file->getPathname())
                    ->all();
            });
        });
    }

    protected function setupScheduler(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new NoxCheckUpdateJob())->hourly();

            $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();
            $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
            $schedule->command(RunHealthChecksCommand::class)->everyMinute();
        });
    }
}
