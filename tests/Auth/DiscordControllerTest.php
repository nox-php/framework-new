<?php

use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery\MockInterface;
use Nox\Framework\Auth\Models\User;
use function Pest\Laravel\get;

beforeEach(fn () => config()->set('nox.auth.discord.enabled', true));

it('redirects to discord when attempting to login when discord is enabled', function () {
    $response = get(route('auth.discord.redirect'));

    $response->assertRedirectContains('https://discord.com');
});

it('creates a user on discord callback when user is verified', function () {
    $socialiteUser = $this->mock(SocialiteUser::class, static function (MockInterface $mock) {
        $mock->shouldReceive('getId')
            ->andReturn(1234567890);

        $mock->shouldReceive('getEmail')
            ->andReturn('test@test.com');

        $mock->shouldReceive('getNickname')
            ->andReturn('Test Nickname');

        $mock->shouldReceive('getName')
            ->andReturn('Test Name');

        $mock->shouldReceive('getAvatar')
            ->andReturn('https://test.com/avatar.jpg');

        $mock->shouldReceive('getRaw')
            ->andReturn([
                'discriminator' => 1234,
                'verified' => true,
            ]);

        $mock->token = 'test_token';
        $mock->refreshToken = 'test_refresh_token';
    });

    $socialiteProvider = $this->mock(SocialiteProvider::class, static function (MockInterface $mock) use ($socialiteUser) {
        $mock->shouldReceive('user')
            ->andReturn($socialiteUser);
    });

    Socialite::shouldReceive('driver')
        ->with('discord')
        ->andReturn($socialiteProvider);

    $response = get(route('auth.discord.callback'));

    $response->assertRedirect('/');

    $user = User::query()
        ->first();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toEqual('Test Name')
        ->and($user->email)->toEqual('test@test.com')
        ->and($user->password)->toBeEmpty()
        ->and($user->discord_id)->toEqual(1234567890)
        ->and($user->discord_token)->toEqual('test_token')
        ->and($user->discord_refresh_token)->toEqual('test_refresh_token')
        ->and($user->discord_discriminator)->toEqual(1234)
        ->and($user->discord_avatar)->toEqual('https://test.com/avatar.jpg')
        ->and($user->hasVerifiedEmail())->toEqual(true);
});

it('redirects back to discord if user is not verified', function () {
    $socialiteUser = $this->mock(SocialiteUser::class, static function (MockInterface $mock) {
        $mock->shouldReceive('getId')
            ->andReturn(1234567890);

        $mock->shouldReceive('getEmail')
            ->andReturn('test@test.com');

        $mock->shouldReceive('getNickname')
            ->andReturn('Test Nickname');

        $mock->shouldReceive('getName')
            ->andReturn('Test Name');

        $mock->shouldReceive('getAvatar')
            ->andReturn('https://test.com/avatar.jpg');

        $mock->shouldReceive('getRaw')
            ->andReturn([
                'discriminator' => 1234,
                'verified' => false,
            ]);

        $mock->token = 'test_token';
        $mock->refreshToken = 'test_refresh_token';
    });

    $socialiteProvider = $this->mock(SocialiteProvider::class, static function (MockInterface $mock) use ($socialiteUser) {
        $mock->shouldReceive('user')
            ->andReturn($socialiteUser);
    });

    Socialite::shouldReceive('driver')
        ->with('discord')
        ->andReturn($socialiteProvider);

    $response = get(route('auth.discord.callback'));

    $response->assertRedirectToRoute('auth.discord.redirect');
});

it('redirects back to the home page if login is cancelled', function () {
    $socialiteProvider = $this->mock(SocialiteProvider::class, static function (MockInterface $mock) {
        $mock->shouldReceive('user')
            ->andReturnNull();
    });

    Socialite::shouldReceive('driver')
        ->with('discord')
        ->andReturn($socialiteProvider);

    $response = get(route('auth.discord.callback'));

    $response->assertRedirect('/');
});
