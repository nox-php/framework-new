<?php

namespace Nox\Framework\Admin\Policies;

use Nox\Framework\Auth\Models\User;
use Nox\Framework\Module\Models\Module;

class ModulePolicy
{
    public function view(User $user): bool
    {
        return $user->can('view', Module::class);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any', Module::class);
    }

    public function create(User $user): bool
    {
        return $user->can('create', Module::class);
    }

    public function update(User $user, Module $model): bool
    {
        return $user->can('update', $model);
    }

    public function restore(User $user, Module $model): bool
    {
        return $user->can('restore', $model);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any', Module::class);
    }

    public function replicate(User $user, Module $model): bool
    {
        return $user->can('replicate', $model);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder', Module::class);
    }

    public function delete(User $user, Module $model): bool
    {
        return $user->can('delete', $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any', Module::class);
    }

    public function forceDelete(User $user, Module $model): bool
    {
        return $user->can('force_delete', $model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any', Module::class);
    }
}
