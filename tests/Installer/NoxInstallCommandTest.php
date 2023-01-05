<?php

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Nox\Framework\Auth\Models\User;

it('can install on sqlite database', function () {
    $database = database_path('database.sqlite');

    File::put($database, '');

    config()->set('database.connections.test', [
        ...config('database.connections.sqlite'),
        'database' => $database,
    ]);

    $this->loadLaravelMigrations('test');

    Event::listen(MigrationsEnded::class, static function () {
        User::factory()->connection('test')->create();
    });

    $this->artisan('nox:install')
        ->expectsQuestion('What is the name of your site?', 'Nox Test')
        ->expectsQuestion('What is the URL of your site?', 'http://nox.test')
        ->expectsChoice('What is this environment?', 'Testing', [
            'Production',
            'Testing',
            'Local',
        ])
        ->expectsConfirmation('Do you want to enable debug mode? This should never be enabled in production', 'yes')
        ->expectsChoice('What database driver do you use?', 'sqlite', [
            'mysql',
            'pgsql',
            'sqlsrv',
            'sqlite',
        ])
        ->expectsQuestion('What is the path of your database?', database_path('database.sqlite'))
        ->expectsOutputToContain('Successfully connected to the database!')
        ->expectsQuestion('What is your Discord client id?', 1234)
        ->expectsQuestion('What is your Discord client secret?', 5678)
        ->expectsConfirmation('Do you want to configure your email settings? You cannot send emails without doing so', 'yes')
        ->expectsChoice('What mail driver do you use?', 'sendmail', [
            'smtp',
            'sendmail',
        ])
        ->expectsQuestion('What is the command for sendmail?', '/usr/sbin/sendmail -bs -i')
        ->expectsOutputToContain('Generating new key')
        ->expectsOutputToContain('Migrating the database')
        ->expectsOutputToContain('Nox database data has been successfully seeded.')
        ->expectsConfirmation('Do you want to create your administrator user?', 'yes')
        ->expectsOutputToContain('Use this link to connect via Discord: http://nox.test/login/discord/redirect')
        ->expectsOutputToContain('Successfully created user')
        ->expectsOutputToContain('Nox has been successfully installed! You can access your new site at http://nox.test')
        ->assertSuccessful();
});
