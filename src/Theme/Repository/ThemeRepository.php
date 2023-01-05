<?php

namespace Nox\Framework\Theme\Repository;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Nox\Framework\Theme\Contracts\ThemeRepository as ThemeRepositoryContract;
use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Exceptions\ThemeNotFoundException;
use Nox\Framework\Theme\Installer\ThemeInstaller;
use Nox\Framework\Theme\Loader\ThemeLoader;
use Nox\Framework\Theme\Theme;

class ThemeRepository implements ThemeRepositoryContract
{
    protected string $directory;

    protected ?array $themes = null;

    protected ?Theme $enabledTheme = null;

    public function __construct(
        protected ThemeLoader $loader,
        protected ThemeInstaller $installer
    ) {
        $this->directory = base_path('themes');
        $this->installer->setPath($this->directory);
    }

    public function all(): array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        if (! $this->loadCache()) {
            $this->load();
        }

        return $this->themes;
    }

    public function enabled(): ?Theme
    {
        return $this->enabledTheme ?? ($this->enabledTheme = collect($this->all())
            ->first(static fn (Theme $theme): bool => $theme->isEnabled()));
    }

    public function disabled(): array
    {
        return collect($this->all())
            ->filter(static fn (Theme $theme): bool => $theme->isDisabled())
            ->all();
    }

    public function find(string $name): ?Theme
    {
        return $this->all()[$name] ?? null;
    }

    public function findOrFail(string $name): Theme
    {
        if ($theme = $this->find($name)) {
            return $theme;
        }

        throw ThemeNotFoundException::theme($name);
    }

    public function enable(Theme|string $theme): ThemeStatus
    {
        if (! $theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        if (! $this->bootTheme($theme)) {
            return ThemeStatus::BootFailed;
        }

        $theme->setEnabled(true);

        settings()->set('nox.themes.enabled', $theme->getName());

        $this->updateCache();

        return ThemeStatus::EnabledSuccess;
    }

    public function disable(): ThemeStatus
    {
        if (! ($name = $this->enabled()?->getName()) || ! $theme = $this->getTheme($name)) {
            return ThemeStatus::NotFound;
        }

        $theme->setEnabled(false);

        settings()->forget('nox.themes.enabled');

        $this->updateCache();

        return ThemeStatus::DisabledSuccess;
    }

    public function boot(): void
    {
        if ($theme = $this->enabled()) {
            $this->bootTheme($theme);
        }
    }

    public function install(string $path, ?string &$name = null): ThemeStatus
    {
        if (! $name = $this->installer->install($path, $status)) {
            return $status;
        }

        $this->clear();

        if (! $theme = $this->find($name)) {
            return ThemeStatus::NotFound;
        }

        if (! $this->bootTheme($theme)) {
            return ThemeStatus::BootFailed;
        }

        if (! $this->installer->publish($theme->getProviders())) {
            return ThemeStatus::PublishFailed;
        }

        return ThemeStatus::InstallSuccess;
    }

    public function delete(Theme|string $theme): ThemeStatus
    {
        if (! $theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        $path = $theme->getPath();

        if (File::exists($path) && ! File::deleteDirectory($path)) {
            return ThemeStatus::DeleteFailed;
        }

        if ($this->enabled()?->getName() === $theme->getName()) {
            settings()->forget('nox.themes.enabled');
        }

        $this->clear();

        return ThemeStatus::DeleteSuccess;
    }

    public function publish(Theme|string $theme, bool $migrate = true): ThemeStatus
    {
        if (! $theme = $this->getTheme($theme)) {
            return ThemeStatus::NotFound;
        }

        if ($this->installer->publish($theme->getProviders(), $migrate)) {
            return ThemeStatus::PublishFailed;
        }

        return ThemeStatus::PublishSuccess;
    }

    protected function bootTheme(Theme $theme): bool
    {
        return rescue(function () use ($theme) {
            if (($parent = $theme->getParent()) && $parentTheme = $this->find($parent)) {
                $this->bootTheme($parentTheme);
            }

            $this->loadFiles($theme->getFiles());
            $this->bootProviders($theme->getProviders());

            return true;
        }, false);
    }

    protected function loadFiles(array $files): void
    {
        foreach ($files as $file) {
            if (File::exists($file)) {
                require_once $file;
            }
        }
    }

    protected function bootProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            app()->register($provider);
        }
    }

    protected function loadCache(): bool
    {
        if (! $this->isCacheEnabled()) {
            return false;
        }

        $key = $this->getCacheKey();

        if ((! $cache = Cache::get($key)) || ! is_array($cache)) {
            return false;
        }

        $this->themes = [];

        foreach ($cache as $cachedTheme) {
            if ($theme = $this->loader->fromArray($cachedTheme)) {
                $this->themes[$theme->getName()] = $theme;
            }
        }

        return true;
    }

    protected function load(): void
    {
        $this->themes = [];

        $manifests = File::search($this->directory, Theme::$MANIFEST_FILE);

        foreach ($manifests as $manifest) {
            if ($theme = $this->loader->fromPath($manifest)) {
                $this->themes[$theme->getName()] = $theme;
            }
        }

        if ($name = settings('nox.themes.enabled')) {
            $this->find($name)?->setEnabled(true);
        }

        $this->updateCache();
    }

    protected function isCacheEnabled(): bool
    {
        return (bool) config('nox.themes.cache.enabled');
    }

    protected function getCacheKey(): string
    {
        return config('nox.themes.cache.key');
    }

    protected function clear(): void
    {
        $this->clearCache();

        $this->themes = null;
        $this->enabledTheme = null;
    }

    protected function clearCache(): void
    {
        if (! $this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forget($key);
    }

    protected function updateCache(): void
    {
        if (! $this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forever(
            $key,
            collect($this->all())
                ->jsonSerialize()
        );
    }

    protected function getTheme(string|Theme $theme): ?Theme
    {
        if ($theme instanceof Theme) {
            return $theme;
        }

        return $this->find($theme);
    }
}
