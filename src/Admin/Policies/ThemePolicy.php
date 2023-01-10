<?php

namespace Nox\Framework\Admin\Policies;

use Nox\Framework\Auth\Models\User;
use Nox\Framework\Theme\Models\Theme;

class ThemePolicy
{
    public function view(User $user): bool
    {
        return $user->can('view', Theme::class);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any', Theme::class);
    }

    public function create(User $user): bool
    {
        return $user->can('create', Theme::class);
    }

    public function update(User $user, Theme $model): bool
    {
        return $user->can('update', $model);
    }

    public function restore(User $user, Theme $model): bool
    {
        return $user->can('restore', $model);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any', Theme::class);
    }

    public function replicate(User $user, Theme $model): bool
    {
        return $user->can('replicate', $model);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder', Theme::class);
    }

    public function delete(User $user, Theme $model): bool
    {
        return $user->can('delete', $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any', Theme::class);
    }

    public function forceDelete(User $user, Theme $model): bool
    {
        return $user->can('force_delete', $model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any', Theme::class);
    }
}
