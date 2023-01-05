<?php

namespace Nox\Framework\Extend\Enums;

enum ModuleStatus: string
{
    case NotFound = 'nox::module.not_found';

    case BootFailed = 'nox::module.boot_failed';

    case PublishSuccess = 'nox::module.publish.success.body';

    case PublishFailed = 'nox::module.publish.failed.body';

    case EnabledSuccess = 'nox::module.enable.success.body';

    case DisabledSuccess = 'nox::module.disable.success.body';

    case InstallSuccess = 'nox::module.install.success.body';

    case InstallFilesNotFound = 'nox::module.install.failed.files_not_found';

    case InstallManifestNotFound = 'nox::module.install.failed.manifest_not_found';

    case InstallManifestLoadFailed = 'nox::module.install.failed.manifest_load_failed';

    case InstallInvalidManifest = 'nox::module.install.failed.invalid_manifest';

    case InstallAlreadyInstalled = 'nox::module.install.failed.already_installed';

    case InstallExtractFailed = 'nox::module.install.failed.extract_failed';

    case DeleteSuccess = 'nox::module.delete.success.body';

    case DeleteFailed = 'nox::module.delete.failed.body';
}
