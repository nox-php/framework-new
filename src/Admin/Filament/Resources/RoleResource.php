<?php

namespace Nox\Framework\Admin\Filament\Resources;

use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Nox\Framework\Admin\Filament\Resources\RoleResource\Pages;
use Silber\Bouncer\Database\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'auth/roles';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return __('nox::admin.resources.role.label');
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->name === 'superadmin' || $record->name === 'user') {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function canForceDelete(Model $record): bool
    {
        if ($record->name === 'superadmin' || $record->name === 'user') {
            return false;
        }

        return parent::canForceDelete($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 3,
                'lg' => null,
            ])
            ->schema([
                Forms\Components\Card::make()
                    ->columns([
                        'sm' => 2,
                    ])
                    ->columnSpan([
                        'sm' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(static fn (?Role $record): bool => $record !== null && ($record->name === 'superadmin' || $record->name === 'user')),
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('is_super_admin')
                            ->label('Is Super Administrator')
                            ->helperText('Super Administrators have unrestricted access and can only be edited by other Super Administrators')
                            ->reactive()
                            ->disabled(static fn (?Role $record): bool => $record !== null && $record->name === 'superadmin')
                            ->afterStateHydrated(static function (Closure $set, ?Role $record) {
                                if ($record === null || ! $record->can('*')) {
                                    $set('is_super_admin', false);

                                    return;
                                }

                                $set('is_super_admin', true);
                            })
                            ->afterStateUpdated(static function (Closure $set, bool $state) {
                                if ($state) {
                                    static::enableAllAbilities($set);
                                }
                            }),
                    ]),
                Forms\Components\Card::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Updated at')
                            ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ]),
                Forms\Components\Tabs::make('Abilities')
                    ->disableLabel()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Resources')
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->schema(static::getResourcesForm()),

                        Forms\Components\Tabs\Tab::make('Pages')
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->schema(static::getPagesForm()),

                        Forms\Components\Tabs\Tab::make('Custom')
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->schema(static::getCustomForm()),
                    ]),
            ]);
    }

    public static function can(string $action, ?Model $record = null): bool
    {
        $user = auth()->user();

        if ($record === null || $user->can('*')) {
            return parent::can($action, $record);
        }

        if ($record->can('*')) {
            return false;
        }

        return parent::can($action, $record);
    }

    protected static function enableAllAbilities(Closure $set): void
    {
        $resources = Filament::getResourceAbilities();
        collect($resources)
            ->each(static function (array $data) use ($set) {
                $set($data['name'], true);

                collect($data['abilities'])
                    ->each(static fn (string $ability) => $set($ability.'//'.$data['name'], true));
            });

        $pages = Filament::getPageAbilities();
        collect($pages)
            ->each(static function (array $data) use ($set) {
                $set($data['name'], true);

                collect($data['abilities'])
                    ->each(static fn (string $ability) => $set($ability.'//'.$data['name'], true));
            });

        $customAbilities = Filament::getCustomAbilities();
        collect($customAbilities)
            ->each(static fn (string $ability) => $set($ability, true));
    }

    protected static function getResourcesForm(): array
    {
        $resources = Filament::getResourceAbilities();

        if (empty($resources)) {
            return [
                Forms\Components\Placeholder::make('no_pages')
                    ->disableLabel()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-center'])
                    ->content('No resources available'),
            ];
        }

        return static::getEntityForm($resources);
    }

    protected static function getEntityForm(array $entities): array
    {
        return collect($entities)
            ->sortKeys()
            ->reduce(static function ($form, $data, $entity) {
                $form[] = Forms\Components\Card::make()
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'border-0 shadow-lg'])
                    ->schema([
                        Forms\Components\Toggle::make($data['name'])
                            ->label(Str::headline(static::getEntityLabel($entity, $data)))
                            ->helperText(new HtmlString('<span class="text-xs">'.($data['model'] ?? $entity).'</span>'))
                            ->onIcon('heroicon-s-lock-open')
                            ->offIcon('heroicon-s-lock-closed')
                            ->disabled(static fn (Closure $get): bool => $get('is_super_admin'))
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateHydrated(static function (Closure $set, ?Role $record) use ($data) {
                                if ($record === null) {
                                    return;
                                }

                                $success = true;
                                foreach ($data['abilities'] as $ability) {
                                    if (! $record->can($ability, $data['model'] ?? null)) {
                                        $success = false;
                                    }
                                }

                                if ($success) {
                                    $set($data['name'], true);
                                }
                            })
                            ->afterStateUpdated(static function (Closure $set, Closure $get, bool $state) use ($data): void {
                                foreach ($data['abilities'] as $ability) {
                                    $set($ability.'//'.$data['name'], $state);
                                }
                            }),

                        Forms\Components\Fieldset::make('Abilities')
                            ->extraAttributes(['class' => 'text-primary-600', 'style' => 'border-color:var(--primary)'])
                            ->columns(2)
                            ->columnSpan(1)
                            ->schema(static::getAbilitiesForm($entity, $data)),
                    ]);

                return $form;
            }, []);
    }

    protected static function getEntityLabel(string $entity, array $data): string
    {
        return rescue(static fn (): string => $entity::getNavigationLabel(), $data['name'], false);
    }

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.resources.role.navigation_label');
    }

    protected static function getAbilitiesForm(string $entity, array $data): array
    {
        return collect($data['abilities'])
            ->reduce(static function ($form, $ability) use ($data) {
                $form[] = Forms\Components\Checkbox::make($ability.'//'.$data['name'])
                    ->label(Str::headline($ability))
                    ->extraAttributes(['class' => 'text-primary-600'])
                    ->reactive()
                    ->disabled(static fn (Closure $get): bool => $get('is_super_admin'))
                    ->dehydrated(static fn (bool $state): bool => $state)
                    ->afterStateHydrated(static function (Closure $set, Closure $get, ?Role $record) use ($ability, $data): void {
                        if ($record === null) {
                            return;
                        }

                        $set(
                            $ability.'//'.$data['name'],
                            $record->can($ability, $data['model'] ?? null)
                        );
                    })
                    ->afterStateUpdated(static function (Closure $set, Closure $get, bool $state) use ($data): void {
                        if (! $state) {
                            $set($data['name'], false);
                        }
                    });

                return $form;
            }, []);
    }

    protected static function getPagesForm(): array
    {
        $pages = Filament::getPageAbilities();

        if (empty($pages)) {
            return [
                Forms\Components\Placeholder::make('no_resources')
                    ->disableLabel()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-center'])
                    ->content('No pages available'),
            ];
        }

        return static::getEntityForm($pages);
    }

    protected static function getCustomForm(): array
    {
        return collect(Filament::getCustomAbilities())
            ->reduce(static function ($form, $ability) {
                $form[] = Forms\Components\Checkbox::make($ability)
                    ->label($ability)
                    ->inline()
                    ->disabled(static fn (Closure $get): bool => $get('is_super_admin'))
                    ->dehydrated(static fn (bool $state): bool => $state)
                    ->afterStateHydrated(static function (Closure $set, Closure $get, ?Role $record) use ($ability) {
                        if ($record === null) {
                            return;
                        }

                        $set($ability, $record->can($ability));
                    });

                return $form;
            }, []);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('title')
                    ->label('Title')
                    ->colors(['primary'])
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('name')
                    ->label('Name'),
                Tables\Columns\BadgeColumn::make('abilities_count')
                    ->label('Abilities')
                    ->counts('abilities')
                    ->colors(['success']),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->date(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return transformer(
            'monet.role.resource.pages',
            [
                'index' => Pages\ListRoles::route('/'),
                'create' => Pages\CreateRole::route('/create'),
                'view' => Pages\ViewRole::route('/{record}'),
                'edit' => Pages\EditRole::route('/{record}/edit'),
            ]
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'title',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->name,
        ];
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.auth');
    }

    protected static function getNavigationBadge(): ?string
    {
        return number_format(Role::query()->count());
    }
}
