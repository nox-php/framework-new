<?php

namespace Nox\Framework\Module\Discovery;

use Composer\InstalledVersions;
use Exception;
use Illuminate\Support\Facades\File;

class ModuleDiscovery
{
    public function discover(): array
    {
        $packages = InstalledVersions::getInstalledPackagesByType('library');

        return $this->getNoxModules($packages);
    }

    protected function getNoxModules(array $packages): array
    {
        return collect($packages)
            ->mapWithKeys(fn(string $package): array => [
                $package => $this->getPackageManifest($package),
            ])
            ->filter()
            ->all();
    }

    protected function getPackageManifest(string $package): ?array
    {
        $path = InstalledVersions::getInstallPath($package);

        $manifestPath = $path . '/composer.json';

        if (!File::exists($manifestPath)) {
            return null;
        }

        $manifest = $this->loadManifest($manifestPath);
        if ($manifest === null || !$this->isNoxModule($manifest)) {
            return null;
        }

        $manifest = collect($manifest)
            ->only([
                'name',
                'description',
                'extra',
            ])
            ->put('path', $path)
            ->put('version', InstalledVersions::getVersion($package))
            ->put('pretty_version', InstalledVersions::getPrettyVersion($package))
            ->all();

        $manifest['config'] = $manifest['extra'];
        unset($manifest['extra']);

        return $manifest;
    }

    protected function loadManifest(string $path): ?array
    {
        try {
            return json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            return null;
        }
    }

    protected function isNoxModule(array $manifest): bool
    {
        if (!isset($manifest['keywords'])) {
            return false;
        }

        return in_array('nox-module', $manifest['keywords']) ||
            in_array('filament', $manifest['keywords']);
    }
}
