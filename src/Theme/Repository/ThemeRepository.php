<?php

namespace Nox\Framework\Theme\Repository;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Nox\Framework\Theme\Contracts\ThemeRepository as ThemeRepositoryContract;
use Nox\Framework\Theme\Discovery\ThemeDiscovery;
use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Exceptions\ThemeNotFoundException;
use Nox\Framework\Theme\Jobs\DeleteThemeJob;
use Nox\Framework\Theme\Jobs\InstallThemeJob;
use Nox\Framework\Theme\Theme;
use Nox\Framework\Updater\Jobs\UpdatePackagistJob;

class ThemeRepository implements ThemeRepositoryContract
{
    protected ?array $themes = null;

    protected ?Theme $enabledTheme = null;

    public function __construct(
        protected ThemeDiscovery $discovery
    )
    {
    }

    public function disabled(): array
    {
        return collect($this->all())
            ->filter(static fn(Theme $theme): bool => !$theme->enabled())
            ->all();
    }

    public function all(): array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        if (!$this->loadCache()) {
            $this->load();
        }

        return $this->themes;
    }

    protected function loadCache(): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        $key = $this->getCacheKey();

        if ((!$cache = Cache::get($key)) || !is_array($cache)) {
            return false;
        }

        $this->themes = collect($cache)
            ->map(static fn($theme): Theme => Theme::fromArray($theme))
            ->all();

        return true;
    }

    protected function isCacheEnabled(): bool
    {
        return (bool)config('nox.themes.cache.enabled');
    }

    protected function getCacheKey(): string
    {
        return config('nox.themes.cache.key');
    }

    protected function load(): void
    {
        $this->themes = collect($this->discovery->discover())
            ->map(static fn(array $manifest): Theme => Theme::fromArray($manifest))
            ->all();

        if ($enabledTheme = settings('monet.themes.enabled')) {
            $this->find($enabledTheme)?->enable();
        }

        $this->updateCache();
    }

    public function enable(Theme|string $theme): ThemeStatus
    {
        if (!$theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        settings()->set('monet.themes.enabled', $theme->name());

        $this->clear();

        return ThemeStatus::EnableSuccess;
    }

    protected function getTheme(string|Theme $theme): ?Theme
    {
        if ($theme instanceof Theme) {
            return $theme;
        }

        return $this->find($theme);
    }

    public function find(string $name): ?Theme
    {
        return $this->all()[$name] ?? null;
    }

    public function clear(): void
    {
        $this->clearCache();

        $this->themes = null;
        $this->enabledTheme = null;
    }

    public function clearCache(): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forget($key);
    }

    protected function updateCache(): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forever(
            $key,
            collect($this->all())
                ->jsonSerialize()
        );
    }

    public function enabled(): ?Theme
    {
        return $this->enabledTheme ?? ($this->enabledTheme = collect($this->all())
            ->first(static fn(Theme $theme): bool => $theme->enabled()));
    }

    public function disable(): ThemeStatus
    {
        settings()->forget('monet.themes.enabled');

        $this->clear();

        return ThemeStatus::DisableSuccess;
    }

    public function findOrFail(string $name): Theme
    {
        if ($theme = $this->find($name)) {
            return $theme;
        }

        throw ThemeNotFoundException::theme($name);
    }

    public function install(string $name): ThemeStatus
    {
        if ($this->getTheme($name) !== null) {
            return ThemeStatus::AlreadyInstalled;
        }

        InstallThemeJob::dispatch($name, auth()->user());

        return ThemeStatus::InstallPending;
    }

    public function delete(Theme|string $theme): ThemeStatus
    {
        if (!$theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        DeleteThemeJob::dispatch($theme->name(), auth()->user());

        return ThemeStatus::DeletePending;
    }

    public function update(Theme|string $theme): ThemeStatus
    {
        if (!$theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        UpdatePackagistJob::dispatch(
            [
                'themes' => $theme->name(),
            ],
            auth()->user()
        );

        return ThemeStatus::UpdatePending;
    }

    public function publish(Theme|string $theme, bool $migrate = true): ThemeStatus
    {
        if (!$theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        return rescue(function () use ($theme, $migrate) {
            $this->bootTheme($theme);
            $this->publishAssets($theme);

            if ($migrate) {
                Artisan::call('migrate');
            }

            return ThemeStatus::PublishSuccess;
        }, ThemeStatus::PublishFailed);
    }

    protected function bootTheme(Theme $theme): void
    {
        foreach ($theme->config('laravel.providers', []) as $provider) {
            if (class_exists($provider)) {
                app()->register($provider);
            }
        }
    }

    protected function publishAssets(Theme $theme): void
    {
        $providers = [
            ...$theme->config('laravel.providers', []),
            ...$theme->config('nox.providers', []),
        ];

        foreach ($providers as $provider) {
            if (!class_exists($provider)) {
                continue;
            }

            $providerInstance = app($provider, [
                'app' => app(),
            ]);

            if (!method_exists($provider, 'getInstallerTags')) {
                continue;
            }

            $tags = (array)(app()->call([$providerInstance, 'getInstallerTags']) ?? []);

            foreach ($tags as $tag => $force) {
                $tag = is_string($tag) ? $tag : $force;
                $force = is_bool($force) ? $force : true;

                Artisan::call('vendor:publish', [
                    '--provider' => $provider,
                    '--tag' => $tag,
                    '--force' => $force,
                ]);
            }
        }
    }

    public function boot(): void
    {
        if (!$theme = $this->enabled()) {
            return;
        }

        if (
            ($parent = $theme->config('nox.theme.parent')) &&
            $parentTheme = $this->find($parent)
        ) {
            $this->bootTheme($parentTheme);
        }

        $this->bootTheme($theme);
    }
}
