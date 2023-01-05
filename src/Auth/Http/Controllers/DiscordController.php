<?php

namespace Nox\Framework\Auth\Http\Controllers;

use Exception;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Nox\Framework\Auth\Models\User;

class DiscordController extends Controller
{
    public function __construct()
    {
        $config = config('nox.auth.discord');

        config()->set('services.discord', [
            ...$config,
            'redirect' => route('auth.discord.callback'),
        ]);
    }

    public function redirect()
    {
        return Socialite::driver('discord')
            ->redirect();
    }

    public function callback()
    {
        if (! $discordUser = $this->getDiscordUser()) {
            return redirect()->intended();
        }

        $raw = $discordUser->getRaw();

        if (! $raw['verified']) {
            return redirect()->route('auth.discord.redirect');
        }

        $user = $this->getOrCreateUser($discordUser, $raw);

        auth()->login($user);

        return app(LoginResponse::class);
    }

    protected function getOrCreateUser(SocialiteUser $discordUser, array $raw): User
    {
        return User::query()
            ->updateOrCreate([
                User::getDiscordIdColumnName() => $discordUser->getId(),
            ], [
                User::getUsernameColumnName() => $discordUser->getName(),
                User::getEmailColumnName() => $discordUser->getEmail(),
                User::getDiscordTokenColumnName() => $discordUser->token,
                User::getDiscordRefreshTokenColumnName() => $discordUser->refreshToken,
                User::getDiscordDiscriminatorColumnName() => $raw['discriminator'],
                User::getDiscordAvatarColumnName() => $discordUser->getAvatar(),
                User::getEmailVerifiedAtColumnName() => now(),
            ]);
    }

    protected function getDiscordUser(): ?SocialiteUser
    {
        try {
            return Socialite::driver('discord')->user();
        } catch (Exception) {
            return null;
        }
    }
}
