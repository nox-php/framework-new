<?php

namespace Nox\Framework\Tests;

use Filament\FilamentServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Nox\Framework\NoxServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Silber\Bouncer\BouncerServiceProvider;
use SocialiteProviders\Manager\ServiceProvider as SocialiteManagerServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            static fn (string $modelName): string => 'Nox\\Framework\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->loadLaravelMigrations();
        $this->artisan('migrate');
    }

    protected function getPackageProviders($app): array
    {
        return [
            BouncerServiceProvider::class,
            SocialiteServiceProvider::class,
            SocialiteManagerServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            NoxServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        Storage::fake('local');

        config()->set('nox.admin.register_theme', false);
    }
}
