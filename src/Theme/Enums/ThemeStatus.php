<?php

namespace Nox\Framework\Theme\Enums;

enum ThemeStatus: string
{
    case NotFound = 'nox::admin.notifications.themes.not_found';

    case AlreadyInstalled = 'nox::admin.notifications.themes.already_installed';

    case EnableSuccess = 'nox::admin.notifications.themes.enable.success.body';

    case DisableSuccess = 'nox::admin.notifications.themes.disable.success.body';

    case PublishSuccess = 'nox::admin.notifications.themes.publish.success.body';

    case PublishFailed = 'nox::admin.notifications.themes.install.failed.body';

    case InstallPending = 'nox::admin.notifications.themes.install.pending.body';

    case DeletePending = 'nox::admin.notifications.themes.delete.pending.body';
}
