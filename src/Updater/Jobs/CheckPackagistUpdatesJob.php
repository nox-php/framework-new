<?php

namespace Nox\Framework\Updater\Jobs;

use Composer\InstalledVersions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Nox\Framework\Module\Facades\Modules;
use Nox\Framework\Support\Packagist;
use Nox\Framework\Theme\Facades\Themes;

class CheckPackagistUpdatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $packages
    ) {
        $this->packages = collect($this->packages)
            ->map(static fn (array|string $package): array => is_array($package) ? $package : [$package])
            ->all();
    }

    public function handle(): void
    {
        $packageNames = Arr::flatten($this->packages);
        $currentVersions = collect($packageNames)
            ->mapWithKeys(static fn (string $packageName): array => [
                $packageName => InstalledVersions::getVersion($packageName),
            ])
            ->all();

        $manifests = Packagist::packages($packageNames);

        foreach ($this->packages as $type => $packages) {
            $versions = $this->getOutdatedPackages($packages, $manifests, $currentVersions);

            $this->updateCache($type, $versions);
        }

        $this->clearCache();
    }

    private function getOutdatedPackages(array $packages, array $manifests, array $currentVersions): array
    {
        $versions = [];

        foreach ($packages as $package) {
            if (! $manifest = $manifests[$package] ?? null) {
                continue;
            }

            if (version_compare($currentVersions[$package], $manifest['version_normalized'], '<')) {
                $versions[$package] = $manifest['version_normalized'];
            }
        }

        return $versions;
    }

    private function updateCache(string $type, array $versions): void
    {
        $cacheKey = 'nox.'.$type.'.updates';

        Cache::forever(
            $cacheKey,
            collect(Cache::get($cacheKey, []))
                ->merge($versions)
                ->all()
        );
    }

    private function clearCache(): void
    {
        if (! empty($this->packages['themes'])) {
            Themes::clear();
        }

        if (! empty($this->packages['modules'])) {
            Modules::clear();
        }
    }
}
