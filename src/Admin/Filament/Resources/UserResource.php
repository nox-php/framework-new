<?php

namespace Nox\Framework\Admin\Filament\Resources;

use Closure;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Nox\Framework\Admin\Filament\Resources\UserResource\Pages;
use Nox\Framework\Auth\Models\User;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'system/users';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.resources.user.navigation_label');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('nox::admin.resources.user.label');
    }

    public static function form(Form $form): Form
    {
        return transformer(
            'nox.user.resource.form',
            $form
                ->columns([
                    'sm' => 3,
                    'lg' => null,
                ])
                ->schema(
                    form([
                        Forms\Components\Card::make()
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpan([
                                'sm' => 2,
                            ])
                            ->schema([
                                Forms\Components\TextInput::make(User::getUsernameColumnName())
                                    ->label(__('nox::admin.resources.user.form.inputs.username'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make(User::getEmailColumnName())
                                    ->label(__('nox::admin.resources.user.form.inputs.email'))
                                    ->required()
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(ignorable: static fn (?User $record): ?User => $record),
                                Forms\Components\Hidden::make('is_super_admin'),
                                Forms\Components\Select::make('roles')
                                    ->label(__('nox::admin.resources.user.form.inputs.roles'))
                                    ->multiple()
                                    ->required()
                                    ->afterStateHydrated(static function (?User $record, Closure $set) {
                                        $set('is_super_admin', $record?->can('*') ?? false);

                                        if ($record === null) {
                                            return;
                                        }

                                        $set('roles', $record->getRoles()->all());
                                    })
                                    ->options(Role::all()->pluck('title', 'name')->all())
                                    ->saveRelationshipsUsing(static function (User $record, Closure $get, array $state) {
                                        $record->roles()->detach();

                                        BouncerFacade::assign($state)->to($record);
                                        BouncerFacade::refreshFor($record);

                                        if (
                                            Filament::auth()->id() === $record->getKey() &&
                                            $get('is_super_admin') &&
                                            ! $record->can('*')
                                        ) {
                                            BouncerFacade::assign('superadmin')->to($record);
                                            BouncerFacade::refreshFor($record);
                                        }
                                    }),
                            ]),
                        Forms\Components\Card::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Placeholder::make('discord_name')
                                    ->label(__('nox::admin.resources.user.form.inputs.discord_name'))
                                    ->content(static fn (?User $record): string => $record->discord_name),
                                Forms\Components\Placeholder::make(User::getCreatedAtColumnName())
                                    ->label(__('nox::admin.resources.user.form.inputs.created_at'))
                                    ->content(static fn (?User $record): string => $record?->{User::getCreatedAtColumnName()}?->diffForHumans() ?? '-'),
                                Forms\Components\Placeholder::make(User::getUpdatedAtColumnName())
                                    ->label(__('nox::admin.resources.user.form.inputs.updated_at'))
                                    ->content(static fn (?User $record): string => $record?->{User::getUpdatedAtColumnName()}?->diffForHumans() ?? '-'),
                            ]),
                    ])->build()
                )
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(
                transformer(
                    'nox.user.resource.table.columns',
                    [
                        Tables\Columns\ImageColumn::make('avatar')
                            ->label(__('nox::admin.resources.user.table.columns.avatar'))
                            ->circular()
                            ->getStateUsing(static function (User $record) {
                                return app(AvatarProvider::class)->get($record);
                            }),
                        Tables\Columns\TextColumn::make(User::getUsernameColumnName())
                            ->label(__('nox::admin.resources.user.table.columns.username'))
                            ->sortable()
                            ->searchable(),
                        Tables\Columns\TextColumn::make(User::getEmailColumnName())
                            ->label(__('nox::admin.resources.user.table.columns.email'))
                            ->sortable()
                            ->searchable(),
                        Tables\Columns\BadgeColumn::make('discord_name')
                            ->label(__('nox::admin.resources.user.table.columns.discord_name'))
                            ->sortable()
                            ->searchable(),
                        Tables\Columns\TextColumn::make(User::getCreatedAtColumnName())
                            ->label(__('nox::admin.resources.user.table.columns.created_at'))
                            ->date()
                            ->sortable()
                            ->searchable(),
                        Tables\Columns\TextColumn::make(User::getUpdatedAtColumnName())
                            ->label(__('nox::admin.resources.user.table.columns.updated_at'))
                            ->date()
                            ->sortable()
                            ->searchable(),
                    ]
                )
            );
    }

    public static function getPages(): array
    {
        return transformer(
            'nox.user.resource.pages',
            [
                'index' => Pages\ListUsers::route('/'),
                'edit' => Pages\EditUser::route('/{record}/edit'),
            ]
        );
    }

    public static function getRelations(): array
    {
        return transformer(
            'nox.user.resource.relations',
            []
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            User::getUsernameColumnName(),
            User::getEmailColumnName(),
            User::getDiscordDiscriminatorColumnName(),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return transformer(
            'nox.themes.resource.title',
            $record->discord_name,
            [
                'user' => $record,
            ]
        );
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return transformer(
            'nox.users.resource.search.details',
            [
                'Email address' => $record->{User::getEmailColumnName()},
                'Created at' => $record->{User::getCreatedAtColumnName()}?->diffForHumans() ?? '-',
            ],
            [
                'user' => $record,
            ]
        );
    }

    protected static function getNavigationBadge(): ?string
    {
        return number_format(User::query()->count());
    }
}
