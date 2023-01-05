<?php

namespace Nox\Framework\Installer\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

trait InstallsComponents
{
    public function publish(array $providers, bool $migrate = true): bool
    {
        return rescue(function () use ($providers, $migrate) {
            $this->publishAssets($providers);

            if ($migrate) {
                Artisan::call('migrate');
            }

            return true;
        }, false);
    }

    protected function publishAssets(array $providers): void
    {
        foreach ($providers as $provider) {
            $providerInstance = app($provider, [
                'app' => app(),
            ]);

            if (! method_exists($provider, 'publishAssets')) {
                continue;
            }

            $tags = (array) (app()->call([$providerInstance, 'publishAssets']) ?? []);

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

    protected function extract(
        ZipArchive $zip,
        string $name,
        string $path
    ): bool {
        return rescue(function () use ($zip, $name, $path) {
            $directory = $this->createDirectory($name, $path);

            $zip->extractTo($directory);

            return true;
        }, false);
    }

    protected function createDirectory(string $name, string $path): string
    {
        $directory = $path.'/'.$name;

        File::ensureDirectoryExists($directory);

        return $directory;
    }

    protected function getManifest(ZipArchive $zip, int $index): ?array
    {
        return rescue(static function () use ($zip, $index) {
            return json_decode($zip->getFromIndex($index), true, 512, JSON_THROW_ON_ERROR);
        });
    }

    protected function findManifestIndex(ZipArchive $zip, string $manifestName): ?int
    {
        if (! ($index = $zip->locateName($manifestName, ZipArchive::FL_NODIR))) {
            return null;
        }

        return $index;
    }

    protected function getArchive(string $path): ?ZipArchive
    {
        if (! File::exists($path)) {
            return null;
        }

        $zip = new ZipArchive();

        if (! $zip->open($path)) {
            return null;
        }

        return $zip;
    }
}
