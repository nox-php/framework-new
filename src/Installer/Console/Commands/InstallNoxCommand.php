<?php

namespace Nox\Framework\Installer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Nox;
use Nox\Framework\Support\Env;
use Silber\Bouncer\BouncerFacade;

class InstallNoxCommand extends Command
{
    protected static string $databaseConnectionName = 'installer';

    protected $signature = 'nox:install {--force : Force the operation to run when already installed}';

    protected $description = 'Install and configure the Nox Framework';

    protected bool $alreadyInstalled = false;

    protected ?array $siteDetails = null;

    protected ?array $databaseDetails = null;

    protected ?array $discordDetails = null;

    protected ?array $emailDetails = null;

    public function handle(): int
    {
        if (
            ($this->alreadyInstalled = Nox::installed()) &&
            ! $this->option('force') &&
            ! $this->confirm('Nox has already been installed. Do you want to continue anyway?')
        ) {
            return 0;
        }

        $this
            ->askSiteDetails()
            ->askDatabaseDetails()
            ->askDiscordDetails()
            ->askEmailDetails()
            ->finish();

        return 0;
    }

    protected function askSiteDetails(): static
    {
        if ($this->alreadyInstalled && ! $this->confirm('Do you want to configure your site details?')) {
            return $this;
        }

        $this->siteDetails = [
            'name' => $this->ask(
                'What is the name of your site?',
                config('app.name')
            ),
            'url' => rtrim(
                $this->ask(
                    'What is the URL of your site?',
                    config('app.url', 'http://localhost')
                ),
                '/'
            ),
            'environment' => $this->choice(
                'What is this environment?',
                [
                    'Production',
                    'Testing',
                    'Local',
                ],
                config('app.environment')
            ),
        ];

        $this->siteDetails['debug'] = $this->confirm(
            'Do you want to enable debug mode? This should never be enabled in production',
            config('app.debug', $this->siteDetails['environment'] !== 'Production')
        );

        return $this;
    }

    protected function askDatabaseDetails(bool $silent = false): static
    {
        if ($this->alreadyInstalled && ! $this->confirm('Do you want to configure your database details?')) {
            return $this;
        }

        $this->databaseDetails = [
            'driver' => $this->choice(
                'What database driver do you use?',
                [
                    'mysql',
                    'pgsql',
                    'sqlsrv',
                    'sqlite',
                ],
                $this->databaseDetails['driver'] ?? 'mysql'
            ),
        ];

        if ($this->databaseDetails['driver'] !== 'sqlite') {
            $this->databaseDetails = [
                ...$this->databaseDetails,
                'host' => $this->ask(
                    'What is the host of your database server?',
                    $this->databaseDetails['host'] ?? 'localhost'
                ),
                'port' => $this->ask(
                    'What is the port of your database server?',
                    $this->databaseDetails['port'] ?? 3306
                ),
                'database' => $this->ask(
                    'What is the name of your database?',
                    $this->databaseDetails['database'] ?? 'nox'
                ),
                'username' => $this->ask(
                    'What is your database username?',
                    $this->databaseDetails['username'] ?? 'root'
                ),
                'password' => $this->secret('What is your database password?', ''),
            ];
        } else {
            $this->databaseDetails = [
                ...$this->databaseDetails,
                'database' => $this->ask('What is the path of your database?'),
            ];
        }

        if (! $this->checkDatabaseDetails()) {
            $this->components->error('Failed to connect to the database, please try again.');
            $this->askDatabaseDetails(true);
        }

        if (! $silent) {
            $this->components->info('Successfully connected to the database!');
        }

        return $this;
    }

    protected function askDiscordDetails(): static
    {
        if ($this->alreadyInstalled && ! $this->confirm('Do you want to configure your discord details?')) {
            return $this;
        }

        $this->discordDetails = [
            'client_id' => $this->ask('What is your Discord client id?'),
            'client_secret' => $this->secret('What is your Discord client secret?'),
        ];

        return $this;
    }

    protected function askEmailDetails(): static
    {
        if (
            ! $this->confirm(
                'Do you want to configure your email settings? You cannot send emails without doing so',
                ! $this->alreadyInstalled
            )
        ) {
            return $this;
        }

        if (
            $this->choice(
                'What mail driver do you use?',
                [
                    'smtp',
                    'sendmail',
                ]
            ) === 'smtp'
        ) {
            $this->emailDetails = [
                'driver' => 'smtp',
                'host' => $this->ask('What is the host of your email server?'),
                'port' => $this->ask('What is the port of your email server?', 587),
                'username' => $this->ask('What is your email username?'),
                'password' => $this->secret('What is your email password?', ''),
                'encryption' => $this->ask('What encryption do you use?', 'tls'),
            ];
        } else {
            $this->emailDetails = [
                'driver' => 'sendmail',
                'path' => $this->ask('What is the command for sendmail?', '/usr/sbin/sendmail -bs -i'),
            ];
        }

        return $this;
    }

    protected function finish(): void
    {
        if (! $this->install()) {
            $this->components->error('Failed to save .env file');

            return;
        }

        if (! $this->alreadyInstalled) {
            $this->components->info('Generating new key');
            $this->call('key:generate', [
                '--force' => true,
            ]);
        }

        if ($this->databaseDetails !== null) {
            $this->components->info('Migrating the database');
            $this->call('migrate', [
                '--force' => true,
                '--database' => static::$databaseConnectionName,
            ]);
        }

        $this->call('nox:seed');

        Storage::put('nox.installed', '');

        $this->createUser();

        $this->components->info(
            sprintf(
                'Nox has been successfully installed! You can access your new site at %s',
                $this->siteDetails === null ? $this->siteDetails['url'] : null
            )
        );
    }

    protected function createUser(): void
    {
        if (! $this->confirm('Do you want to create your administrator user?', true)) {
            return;
        }

        if ($this->siteDetails === null) {
            $url = route('auth.discord.redirect');
        } else {
            $url = str_replace(
                'http://localhost',
                rtrim($this->siteDetails['url'], '/\\'),
                route('auth.discord.redirect')
            );
        }

        $this->components->info(
            'Use this link to connect via Discord: '.$url
        );

        $time = now();

        $user = null;
        while ($user === null) {
            $user = User::on($this->databaseDetails === null ? null : static::$databaseConnectionName)
                ->whereNotNull(User::getDiscordIdColumnName())
                ->where(User::getCreatedAtColumnName(), '>=', $time)
                ->first();

            sleep(1);
        }

        BouncerFacade::assign('superadmin')->to($user);

        $this->components->info('Successfully created user');
    }

    protected function install(): bool
    {
        File::ensureDirectoryExists(base_path('modules'));
        File::ensureDirectoryExists(base_path('themes'));

        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            File::put($envPath, '');
        }

        $env = new Env($envPath);

        $updated = false;

        if ($this->siteDetails !== null) {
            $env->put([
                'APP_NAME' => $this->siteDetails['name'],
                'APP_ENV' => Str::lower($this->siteDetails['environment']),
                'APP_KEY' => '',
                'APP_DEBUG' => $this->siteDetails['debug'] ? 'true' : 'false',
                'APP_URL' => $this->siteDetails['url'],
            ]);

            $updated = true;
        }

        if ($this->databaseDetails !== null) {
            $env->put([
                'DB_CONNECTION' => $this->databaseDetails['driver'],
            ]);

            if ($this->databaseDetails['driver'] !== 'sqlite') {
                $env->put([
                    'DB_HOST' => $this->databaseDetails['host'],
                    'DB_PORT' => $this->databaseDetails['port'],
                    'DB_DATABASE' => $this->databaseDetails['database'],
                    'DB_USERNAME' => $this->databaseDetails['username'],
                    'DB_PASSWORD' => $this->databaseDetails['password'],
                ]);
            } else {
                $env->put([
                    'DB_DATABASE' => $this->databaseDetails['database'],
                ]);
            }

            $updated = true;
        }

        if ($this->discordDetails !== null) {
            $env->put([
                'DISCORD_CLIENT_ID' => $this->discordDetails['client_id'],
                'DISCORD_CLIENT_SECRET' => $this->discordDetails['client_secret'],
            ]);

            $updated = true;
        }

        if ($this->emailDetails !== null) {
            $env->put([
                'MAIL_MAILER' => $this->emailDetails['driver'],
            ]);

            if ($this->emailDetails['driver'] !== 'sendmail') {
                $env->put([
                    'MAIL_HOST' => $this->emailDetails['host'],
                    'MAIL_PORT' => $this->emailDetails['port'],
                    'MAIL_USERNAME' => $this->emailDetails['username'],
                    'MAIL_PASSWORD' => $this->emailDetails['password'],
                    'MAIL_ENCRYPTION' => $this->emailDetails['encryption'],
                    'MAIL_FROM_ADDRESS' => $this->emailDetails['from']['address'],
                    'MAIL_FROM_NAME' => $this->emailDetails['from']['name'],
                ]);
            } else {
                $env->put([
                    'MAIL_SENDMAIL_PATH' => $this->emailDetails['path'],
                ]);
            }

            $updated = true;
        }

        if ($updated) {
            return $env->save();
        }

        return true;
    }

    protected function checkDatabaseDetails(): bool
    {
        $config = config('database.connections.'.$this->databaseDetails['driver'], []);

        $config = [
            ...$config,
            ...$this->databaseDetails,
        ];

        config()->set('database.connections.installer', $config);

        return rescue(static function () {
            DB::connection(static::$databaseConnectionName)->getPdo();

            return true;
        }, static function () {
            DB::connection(static::$databaseConnectionName)->disconnect();

            return false;
        });
    }
}
