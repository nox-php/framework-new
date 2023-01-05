<?php

namespace Nox\Framework\Extend\Installer;

use Nox\Framework\Extend\Enums\ModuleStatus;
use Nox\Framework\Extend\Facades\Modules;
use Nox\Framework\Extend\Loader\ModuleLoader;
use Nox\Framework\Extend\Module;
use Nox\Framework\Installer\Traits\InstallsComponents;

class ModuleInstaller
{
    use InstallsComponents;

    protected string $path;

    public function __construct(
        protected ModuleLoader $loader,
        ?string $path = null
    ) {
        $this->path = $path ?? base_path('modules');
    }

    public function install(string $path, ?ModuleStatus &$status = null): ?string
    {
        if (! $zip = $this->getArchive($path)) {
            $status = ModuleStatus::InstallFilesNotFound;

            return null;
        }

        if (! $index = $this->findManifestIndex($zip, Module::$MANIFEST_FILE)) {
            $status = ModuleStatus::InstallManifestNotFound;

            return null;
        }

        if (! $manifest = $this->getManifest($zip, $index)) {
            $status = ModuleStatus::InstallManifestLoadFailed;

            return null;
        }

        $manifest['path'] = $path;

        if (! $this->loader->validate($manifest)) {
            $status = ModuleStatus::InstallInvalidManifest;

            return null;
        }

        $name = $manifest['name'];

        if (Modules::find($name) !== null) {
            $status = ModuleStatus::InstallAlreadyInstalled;

            return null;
        }

        if (! $this->extract($zip, $name, $this->path)) {
            $status = ModuleStatus::InstallExtractFailed;

            return null;
        }

        $status = ModuleStatus::InstallSuccess;

        return $name;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}
