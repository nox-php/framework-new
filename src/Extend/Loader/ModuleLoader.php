<?php

namespace Nox\Framework\Extend\Loader;

use Illuminate\Support\Facades\File;
use Nox\Framework\Extend\Module;

class ModuleLoader
{
    public function fromPath(string $path): ?Module
    {
        $manifest = [
            ...$this->loadManifest($path),
            'path' => dirname($path),
        ];

        return $this->fromArray($manifest);
    }

    public function fromArray(array $manifest): ?Module
    {
        if (! $this->validate($manifest)) {
            return null;
        }

        $files = [
            ...$manifest['files'] ?? [],
            'vendor/autoload.php',
        ];

        return new Module(
            $manifest['name'],
            $manifest['description'],
            $manifest['version'],
            $manifest['path'],
            $files,
            $manifest['providers'] ?? [],
            $manifest['priority'] ?? 1,
            $manifest['enabled'] ?? false
        );
    }

    public function validate(array $manifest): bool
    {
        return ! empty($manifest['name']) &&
            ! empty($manifest['description']) &&
            ! empty($manifest['version']) &&
            ! empty($manifest['path']);
    }

    protected function loadManifest(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        return rescue(static function () use ($path) {
            return json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        }, []);
    }
}
