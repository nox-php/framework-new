<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->string('password')->default('')->change();
            $table->string('discord_id')->nullable()->unique()->after('email_verified_at');
            $table->string('discord_token')->nullable()->after('discord_id');
            $table->string('discord_refresh_token')->nullable()->after('discord_token');
            $table->integer('discord_discriminator')->nullable()->after('discord_refresh_token');
            $table->text('discord_avatar')->nullable()->after('discord_discriminator');
        });
    }
};
