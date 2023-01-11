<?php

namespace Nox\Framework\Admin\Policies;

use Nox\Framework\Auth\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    public function view(User $user): bool
    {
        return $user->can('view', Activity::class);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any', Activity::class);
    }

    public function create(User $user): bool
    {
        return $user->can('create', Activity::class);
    }

    public function update(User $user, Activity $model): bool
    {
        return $user->can('update', $model);
    }

    public function restore(User $user, Activity $model): bool
    {
        return $user->can('restore', $model);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any', Activity::class);
    }

    public function replicate(User $user, Activity $model): bool
    {
        return $user->can('replicate', $model);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder', Activity::class);
    }

    public function delete(User $user, Activity $model): bool
    {
        if ($model->name === 'superadmin' || $model->name === 'user') {
            return false;
        }

        return $user->can('delete', $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any', Activity::class);
    }

    public function forceDelete(User $user, Activity $model): bool
    {
        if ($model->name === 'superadmin' || $model->name === 'user') {
            return false;
        }

        return $user->can('force_delete', $model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any', Activity::class);
    }
}
