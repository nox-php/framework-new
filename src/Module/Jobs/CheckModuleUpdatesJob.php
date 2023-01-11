<?php

namespace Nox\Framework\Module\Jobs;

use Composer\InstalledVersions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Nox\Framework\Support\Packagist;
use Nox\Framework\Theme\Facades\Themes;
use Nox\Framework\Theme\Theme;

class CheckModuleUpdatesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $versions = $this->getLatestVersions();

        Cache::forever('nox.themes.updates', $versions);
    }

    private function getLatestVersions(): array
    {
        $themes = collect(Themes::all())
            ->mapWithKeys(static fn (Theme $theme): array => [
                $theme->name() => InstalledVersions::getVersion($theme->name()),
            ])
            ->all();

        $manifests = Packagist::packages(array_keys($themes));

        $versions = [];
        foreach ($manifests as $manifest) {
            if (version_compare($themes[$manifest['name']], $manifest['version_normalized'], '<')) {
                $versions[$manifest['name']] = $manifest['version_normalized'];
            }
        }

        return $versions;
    }
}
