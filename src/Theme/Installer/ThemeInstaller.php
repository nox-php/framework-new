<?php

namespace Nox\Framework\Theme\Installer;

use Nox\Framework\Installer\Traits\InstallsComponents;
use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Facades\Themes;
use Nox\Framework\Theme\Loader\ThemeLoader;
use Nox\Framework\Theme\Theme;

class ThemeInstaller
{
    use InstallsComponents;

    protected string $path;

    public function __construct(
        protected ThemeLoader $loader,
        ?string $path = null
    ) {
        $this->path = $path ?? base_path('themes');
    }

    public function install(string $path, ?ThemeStatus &$status = null): ?string
    {
        if (! $zip = $this->getArchive($path)) {
            $status = ThemeStatus::InstallFilesNotFound;

            return null;
        }

        if (! $index = $this->findManifestIndex($zip, Theme::$MANIFEST_FILE)) {
            $status = ThemeStatus::InstallManifestNotFound;

            return null;
        }

        if (! $manifest = $this->getManifest($zip, $index)) {
            $status = ThemeStatus::InstallManifestLoadFailed;

            return null;
        }

        $manifest['path'] = $path;

        if (! $this->loader->validate($manifest)) {
            $status = ThemeStatus::InstallInvalidManifest;

            return null;
        }

        $name = $manifest['name'];

        if (Themes::find($name) !== null) {
            $status = ThemeStatus::InstallAlreadyInstalled;

            return null;
        }

        if (! empty($manifest['parent']) && ! Themes::find($manifest['parent'])) {
            $status = ThemeStatus::InstallParentNotFound;

            return null;
        }

        if (! $this->extract($zip, $name, $this->path)) {
            $status = ThemeStatus::InstallExtractFailed;

            return null;
        }

        $status = ThemeStatus::InstallSuccess;

        return $name;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}
