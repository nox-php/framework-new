<?php

namespace Nox\Framework\Admin\Policies;

use Nox\Framework\Auth\Models\User;

class UserPolicy
{
    public function view(User $user): bool
    {
        return $user->can('view', User::class);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any', User::class);
    }

    public function create(User $user): bool
    {
        return $user->can('create', User::class);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('update', $model);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('restore', $model);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any', User::class);
    }

    public function replicate(User $user, User $model): bool
    {
        return $user->can('replicate', $model);
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder', User::class);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->canDelete($user, $model) &&
            $user->can('delete', $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any', User::class);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->canDelete($user, $model) &&
            $user->can('force_delete', $model);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any', User::class);
    }

    private function canDelete(User $user, User $model): bool
    {
        if($user->can('*')) {
            return true;
        }

        if($model->can('*')) {
            return false;
        }

        return $user->getAuthIdentifier() !== $model->getAuthIdentifier();
    }
}
