<?php

namespace Nox\Framework\Admin\Policies;

use Nox\Framework\Auth\Models\User;
use Silber\Bouncer\Database\Role;

class RolePolicy
{
    public function view(User $user): bool
    {
        return $user->can('view', Role::class);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any', Role::class);
    }

    public function create(User $user): bool
    {
        return $user->can('create', Role::class);
    }

    public function update(User $user, Role $model): bool
    {
        return $user->can('update', $model);
    }

    public function restore(User $user, Role $model): bool
    {
        return $user->can('restore', $model);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any', Role::class);
    }

    public function replicate(User $user, Role $model): bool
    {
        return $user->can('replicate', $model);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder', Role::class);
    }

    public function delete(User $user, Role $model): bool
    {
        if($model->name === 'superadmin' || $model->name === 'user') {
            return false;
        }

        return $user->can('delete', $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any', Role::class);
    }

    public function forceDelete(User $user, Role $model): bool
    {
        if($model->name === 'superadmin' || $model->name === 'user') {
            return false;
        }

        return $user->can('force_delete', $model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any', Role::class);
    }
}
