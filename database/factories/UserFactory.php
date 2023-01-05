<?php

namespace Nox\Framework\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nox\Framework\Auth\Models\User;

class UserFactory extends Factory
{
    public function modelName(): string
    {
        return User::class;
    }

    public function definition(): array
    {
        return [
            User::getUsernameColumnName() => $this->faker->name(),
            User::getEmailColumnName() => $this->faker->safeEmail(),
            User::getEmailVerifiedAtColumnName() => $this->faker->dateTime(),
            User::getDiscordIdColumnName() => $this->faker->randomNumber(),
            User::getDiscordTokenColumnName() => $this->faker->text(20),
            User::getDiscordRefreshTokenColumnName() => $this->faker->text(20),
            User::getDiscordDiscriminatorColumnName() => $this->faker->randomNumber(4),
            User::getDiscordAvatarColumnName() => $this->faker->imageUrl(),
        ];
    }
}
