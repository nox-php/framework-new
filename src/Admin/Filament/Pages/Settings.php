<?php

namespace Nox\Framework\Admin\Filament\Pages;

use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nox\Framework\Support\Env;
use Nox\Framework\Updater\Jobs\NoxCheckUpdateJob;
use Nox\Framework\Updater\Jobs\NoxUpdateJob;

class Settings extends Page
{
    protected static string $view = 'nox::filament.pages.settings';

    protected static ?string $slug = 'system/settings';

    protected static ?int $navigationSort = 100;

    public ?string $availableUpdateVersion = null;

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.pages.settings.label');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.system');
    }

    protected function getTitle(): string
    {
        return __('nox::admin.pages.settings.label');
    }

    public function mount(): void
    {
        $databaseConfig = collect(config('database.connections.'.config('database.default'), []))
            ->only([
                'driver',
                'host',
                'port',
                'database',
                'username',
            ])
            ->mapWithKeys(static fn ($value, $key): array => [
                'database_'.$key => $value,
            ])
            ->all();

        $mailConfig = collect(config('mail.mailers.'.config('mail.default'), []))
            ->only([
                'transport',
                'host',
                'port',
                'username',
                'password',
                'encryption',
                'path',
            ])
            ->mapWithKeys(static fn ($value, $key): array => [
                'mail_'.$key => $value,
            ])
            ->all();

        $state = [
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'site_environment' => config('app.env'),
            'site_debug' => config('app.debug'),

            ...$databaseConfig,
            'database_password_empty' => empty($databaseConfig['database_password']),

            'discord_client_id' => config('nox.auth.discord.client_id'),

            ...$mailConfig,
            'mail_password_empty' => empty($databaseConfig['mail_password']),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];

        $this->form->fill($state);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $databaseConfig = collect($state)
            ->mapWithKeys(static fn ($value, $key): array => [
                Str::replace('database_', '', $key) => $value,
            ])
            ->all();

        if (! $this->testDatabaseConnection($databaseConfig)) {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.settings.failed.database.title'))
                ->body(__('nox::admin.notifications.settings.failed.database.body'))
                ->send();

            return;
        }

        $env = (new Env())
            ->put([
                'APP_NAME' => $state['site_name'],
                'APP_ENV' => $state['site_environment'],
                'APP_DEBUG' => $state['site_debug'] ? 'true' : 'false',
                'APP_URL' => rtrim($state['site_url'], '/'),

                'DB_CONNECTION' => $state['database_driver'],
            ]);

        if ($state['database_driver'] === 'sqlite') {
            $env->put('DB_PASSWORD', $state['database_database']);
        } else {
            $env->put([
                'DB_HOST' => $state['database_host'],
                'DB_PORT' => $state['database_port'],
                'DB_DATABASE' => $state['database_database'],
                'DB_USERNAME' => $state['database_username'],
            ]);

            if (array_key_exists('database_password', $state)) {
                $env->put('DB_PASSWORD', $state['database_password'] ?? '');
            }
        }

        $env->put('DISCORD_CLIENT_ID', $state['discord_client_id']);

        if (array_key_exists('discord_client_secret', $state)) {
            $env->put('DISCORD_CLIENT_SECRET', $state['discord_client_id']);
        }

        $env->put('MAIL_MAILER', $state['mail_transport']);

        if ($state['mail_transport'] === 'sendmail') {
            $env->put('MAIL_SENDMAIL_PATH', $state['mail_path']);
        } else {
            $env->put([
                'MAIL_HOST' => $state['mail_host'],
                'MAIL_PORT' => $state['mail_port'],
                'MAIL_USERNAME' => $state['mail_username'],
                'MAIL_ENCRYPTION' => $state['mail_encryption'],
                'MAIL_FROM_ADDRESS' => $state['mail_from_address'],
                'MAIL_FROM_NAME' => $state['mail_from_name'],
            ]);

            if (array_key_exists('mail_password', $state)) {
                $env->put('MAIL_PASSWORD', $state['mail_password'] ?? '');
            }
        }

        if ($env->save()) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.settings.success.title'))
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.settings.failed.config.title'))
                ->body(__('nox::admin.notifications.settings.failed.config.body'))
                ->send();
        }
    }

    protected function testDatabaseConnection(array $state): bool
    {
        $config = config('database.connections.'.$state['driver'], []);

        $config = [
            ...$config,
            ...$state,
        ];

        config()->set('database.connections.settings_test', $config);

        return rescue(static function () {
            DB::connection('settings_test')->getPdo();

            return true;
        }, static function () {
            DB::connection('settings_test')->disconnect();

            return false;
        });
    }

    protected function getViewData(): array
    {
        $this->availableUpdateVersion = Cache::get('nox.updater.available');

        return parent::getViewData();
    }

    public function installUpdate(): void
    {
        if ($this->availableUpdateVersion === null) {
            return;
        }

        NoxUpdateJob::dispatch(Filament::auth()->user(), $this->availableUpdateVersion);

        Notification::make()
            ->success()
            ->title(__('nox::admin.notifications.nox_update.updating.title', ['version' => $this->availableUpdateVersion]))
            ->body(__('nox::admin.notifications.nox_update.updating.body'))
            ->send();
    }

    public function checkUpdate(): void
    {
        NoxCheckUpdateJob::dispatch(Filament::auth()->user());

        Notification::make()
            ->success()
            ->title(__('nox::admin.notifications.settings.check_updates.title'))
            ->body(__('nox::admin.notifications.settings.check_updates.body'))
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Action::make('check-nox-update')
                ->label(__('nox::admin.pages.settings.actions.check_for_updates'))
                ->button()
                ->action('checkUpdate'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')
                ->disableLabel()
                ->tabs([
                    Tabs\Tab::make(__('nox::admin.pages.settings.form.tabs.site'))
                        ->schema([
                            Fieldset::make(__('nox::admin.pages.settings.form.fieldsets.global'))
                                ->schema([
                                    TextInput::make('site_name')
                                        ->label(__('nox::admin.pages.settings.form.inputs.site_name.label'))
                                        ->required()
                                        ->hint(__('nox::admin.pages.settings.form.inputs.site_name.hint'))
                                        ->maxLength(255),
                                    TextInput::make('site_url')
                                        ->label(__('nox::admin.pages.settings.form.inputs.site_url.label'))
                                        ->required()
                                        ->hint(__('nox::admin.pages.settings.form.inputs.site_name.hint'))
                                        ->maxLength(255),
                                ]),
                            Fieldset::make(__('nox::admin.pages.settings.form.fieldsets.debugging'))
                                ->schema([
                                    Select::make('site_environment')
                                        ->label(__('nox::admin.pages.settings.form.inputs.site_environment.label'))
                                        ->required()
                                        ->options([
                                            'production' => __('nox::admin.pages.settings.form.inputs.site_environment.options.production'),
                                            'testing' => __('nox::admin.pages.settings.form.inputs.site_environment.options.testing'),
                                            'local' => __('nox::admin.pages.settings.form.inputs.site_environment.options.local'),
                                        ]),
                                    Toggle::make('site_debug')
                                        ->label(__('nox::admin.pages.settings.form.inputs.site_debug.label'))
                                        ->helperText(__('nox::admin.pages.settings.form.inputs.site_debug.helper')),
                                ]),
                        ]),
                    Tabs\Tab::make(__('nox::admin.pages.settings.form.tabs.database'))
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Select::make('database_driver')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_driver.label'))
                                        ->reactive()
                                        ->required()
                                        ->options([
                                            'mysql' => __('nox::admin.pages.settings.form.inputs.database_driver.options.mysql'),
                                            'pgsql' => __('nox::admin.pages.settings.form.inputs.database_driver.options.pgsql'),
                                            'sqlsrv' => __('nox::admin.pages.settings.form.inputs.database_driver.options.sqlsrv'),
                                            'sqlite' => __('nox::admin.pages.settings.form.inputs.database_driver.options.sqlite'),
                                        ]),
                                    TextInput::make('database_host')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_host'))
                                        ->required(static fn (Closure $get): bool => $get('database_driver') !== 'sqlite')
                                        ->hidden(static fn (Closure $get): bool => $get('database_driver') === 'sqlite'),
                                    TextInput::make('database_port')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_port'))
                                        ->integer()
                                        ->minValue(1)
                                        ->required(static fn (Closure $get): bool => $get('database_driver') !== 'sqlite')
                                        ->hidden(static fn (Closure $get): bool => $get('database_driver') === 'sqlite'),
                                    TextInput::make('database_database')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_database'))
                                        ->required(),
                                    TextInput::make('database_username')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_username'))
                                        ->required(static fn (Closure $get): bool => $get('database_driver') !== 'sqlite')
                                        ->hidden(static fn (Closure $get): bool => $get('database_driver') === 'sqlite'),
                                    Hidden::make('database_password_empty')
                                        ->default(false)
                                        ->reactive(),
                                    TextInput::make('database_password')
                                        ->label(__('nox::admin.pages.settings.form.inputs.database_password'))
                                        ->password()
                                        ->dehydrated(fn (Closure $get, $state) => filled($state) || $get('database_password_empty'))
                                        ->disabled(static fn (Closure $get) => $get('database_password_empty') === true)
                                        ->required(static fn (Closure $get): bool => $get('database_driver') !== 'sqlite' && $get('database_password_empty') === false)
                                        ->hidden(static fn (Closure $get): bool => $get('database_driver') === 'sqlite')
                                        ->suffixAction(static function (Closure $get, Closure $set) {
                                            if ($get('database_password_empty') === true) {
                                                return \Filament\Forms\Components\Actions\Action::make('empty-database-password')
                                                    ->icon('heroicon-o-plus')
                                                    ->action(static function () use ($set) {
                                                        $set('database_password_empty', false);
                                                    });
                                            }

                                            return \Filament\Forms\Components\Actions\Action::make('empty-database-password')
                                                ->icon('heroicon-o-minus')
                                                ->action(static function () use ($set) {
                                                    $set('database_password_empty', true);
                                                });
                                        }),
                                ]),
                        ]),
                    Tabs\Tab::make(__('nox::admin.pages.settings.form.tabs.discord'))
                        ->schema([
                            Grid::make()
                                ->schema([
                                    TextInput::make('discord_client_id')
                                        ->label(__('nox::admin.pages.settings.form.inputs.discord_client_id'))
                                        ->required(),
                                    TextInput::make('discord_client_secret')
                                        ->label(__('nox::admin.pages.settings.form.inputs.discord_client_secret'))
                                        ->required(static fn (): bool => config('nox.auth.discord.client_secret') === null)
                                        ->password()
                                        ->dehydrated(fn ($state) => filled($state)),
                                ]),
                        ]),
                    Tabs\Tab::make(__('nox::admin.pages.settings.form.tabs.mail'))
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Fieldset::make(__('nox::admin.pages.settings.form.fieldsets.credentials'))
                                        ->schema([
                                            Select::make('mail_transport')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_transport.label'))
                                                ->reactive()
                                                ->required()
                                                ->options([
                                                    'smtp' => __('nox::admin.pages.settings.form.inputs.mail_transport.options.smtp'),
                                                    'sendmail' => __('nox::admin.pages.settings.form.inputs.mail_transport.options.sendmail'),
                                                ]),
                                            TextInput::make('mail_path')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_path'))
                                                ->default('/usr/sbin/sendmail -bs -i')
                                                ->required(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail')
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') !== 'sendmail'),
                                            TextInput::make('mail_host')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_host'))
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail'),
                                            TextInput::make('mail_port')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_port'))
                                                ->integer()
                                                ->minValue(1)
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail'),
                                            TextInput::make('mail_username')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_username'))
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail'),
                                            Hidden::make('mail_password_empty')
                                                ->default(false)
                                                ->reactive(),
                                            TextInput::make('mail_password')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_password'))
                                                ->password()
                                                ->dehydrated(fn (Closure $get, $state) => filled($state) || $get('mail_password_empty'))
                                                ->disabled(static fn (Closure $get) => $get('mail_password_empty') === true)
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail')
                                                ->suffixAction(static function (Closure $get, Closure $set) {
                                                    if ($get('mail_password_empty') === true) {
                                                        return \Filament\Forms\Components\Actions\Action::make('empty-mail-password')
                                                            ->icon('heroicon-o-plus')
                                                            ->action(static function () use ($set) {
                                                                $set('mail_password_empty', false);
                                                            });
                                                    }

                                                    return \Filament\Forms\Components\Actions\Action::make('empty-mail-password')
                                                        ->icon('heroicon-o-minus')
                                                        ->action(static function () use ($set) {
                                                            $set('mail_password_empty', true);
                                                        });
                                                }),
                                            TextInput::make('mail_encryption')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_encryption'))
                                                ->hidden(static fn (Closure $get): bool => $get('mail_transport') === 'sendmail')
                                                ->default('tls'),
                                        ]),
                                    Fieldset::make(__('nox::admin.pages.settings.form.fieldsets.signature'))
                                        ->schema([
                                            TextInput::make('mail_from_address')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_from_address')),
                                            TextInput::make('mail_from_name')
                                                ->label(__('nox::admin.pages.settings.form.inputs.mail_from_name')),
                                        ]),
                                ]),
                        ]),
                ]),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return Cache::has('nox.updater.available')
            ? '1'
            : null;
    }
}
