<?php

namespace Nox\Framework\Extend\Discovery;

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
            ->mapWithKeys(static fn(string $package): array => [
                $package => $this->getPackageManifest($package)
            ])
            ->filter()
            ->all();
    }

    protected function getPackageManifest(string $package): ?array
    {
        $path = $package . '/composer.json';

        if (!File::exists($path)) {
            return null;
        }

        $manifest = $this->loadManifest($path);
        if($manifest === null || !$this->isNoxModule($manifest)) {
            return null;
        }

        return collect($manifest)
            ->only([
                'name',
                'version',
                'config'
            ])
            ->put('path', $package)
            ->all();
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

        return in_array('nox-package', $manifest['keywords']);
    }
}
