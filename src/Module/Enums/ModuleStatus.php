<?php

namespace Nox\Framework\Module\Enums;

enum ModuleStatus: string
{
    case NotFound = 'nox::admin.notifications.modules.not_found';

    case PublishSuccess = 'nox::admin.notifications.modules.publish.success.body';

    case PublishFailed = 'nox::admin.notifications.modules.install.failed.body';

    case InstallPending = 'nox::admin.notifications.modules.install.pending.body';

    case InstallSuccess = 'nox::admin.notifications.modules.install.success.body';

    case AlreadyInstalled = 'nox::admin.notifications.modules.already_installed';

    case DeletePending = 'nox::admin.notifications.modules.delete.pending.body';

    case DeleteSuccess = 'nox::admin.notifications.modules.delete.success.body';

    case DeleteFailed = 'nox::admin.notifications.modules.delete.failed.body';
}
