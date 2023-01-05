<?php

namespace Nox\Framework\Theme\Enums;

enum ThemeStatus: string
{
    case NotFound = 'nox::theme.not_found';

    case BootFailed = 'nox::theme.boot_failed';

    case PublishSuccess = 'nox::theme.publish.success.body';

    case PublishFailed = 'nox::theme.publish.failed.body';

    case EnabledSuccess = 'nox::theme.enable.success.body';

    case DisabledSuccess = 'nox::theme.disable.success.body';

    case InstallSuccess = 'nox::theme.install.success.body';

    case InstallFilesNotFound = 'nox::theme.install.failed.files_not_found';

    case InstallManifestNotFound = 'nox::theme.install.failed.manifest_not_found';

    case InstallManifestLoadFailed = 'nox::theme.install.failed.manifest_load_failed';

    case InstallInvalidManifest = 'nox::theme.install.failed.invalid_manifest';

    case InstallAlreadyInstalled = 'nox::theme.install.failed.already_installed';

    case InstallExtractFailed = 'nox::theme.install.failed.extract_failed';

    case InstallParentNotFound = 'nox::theme.install.failed.parent_not_found';

    case DeleteSuccess = 'nox::theme.delete.success.body';

    case DeleteFailed = 'nox::theme.delete.failed.body';
}
