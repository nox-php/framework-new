<?php

namespace Nox\Framework\Admin\Filament\AvatarProvider;

use Filament\AvatarProviders\UiAvatarsProvider;
use Illuminate\Database\Eloquent\Model;

class AvatarProvider extends UiAvatarsProvider
{
    public function get(Model $user): string
    {
        if (! empty($user->discord_avatar)) {
            return $user->discord_avatar;
        }

        return parent::get($user);
    }
}
