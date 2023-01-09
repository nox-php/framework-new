<?php

namespace Nox\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;
use Nox\Framework\Theme\Models\Theme;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static ?string $slug = 'appearance/themes';

    protected static ?string $navigationIcon = 'heroicon-o-template';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('nox::admin.resources.theme.label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('nox::admin.resources.theme.form.inputs.name')),
                Forms\Components\TextInput::make('version')
                    ->label(__('nox::admin.resources.theme.form.inputs.version'))
                    ->formatStateUsing(static fn(string $state): string => 'v' . $state),
                Forms\Components\TextInput::make('path')
                    ->label(__('nox::admin.resources.theme.form.inputs.path'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(__('nox::admin.resources.theme.form.inputs.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('nox::admin.resources.theme.table.columns.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('nox::admin.resources.theme.table.columns.description'))
                    ->sortable()
                    ->searchable()
                    ->limit(),
                Tables\Columns\BadgeColumn::make('version')
                    ->label(__('nox::admin.resources.theme.table.columns.version'))
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make()
                    ->label(__('nox::admin.resources.theme.table.actions.enable'))
                    ->requiresConfirmation()
                    ->action('enableTheme')
                    ->hidden(static fn(Theme $theme): bool => $theme->enabled),
                Tables\Actions\Action::make()
                    ->label(__('nox::admin.resources.theme.table.actions.disable'))
                    ->requiresConfirmation()
                    ->action('disableTheme')
                    ->hidden(static fn(Theme $theme): bool => !$theme->enabled),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->action('deleteTheme'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action('bulkDeleteThemes'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThemes::route('/'),
            'browse' => Pages\BrowseThemes::route('/browse'),
            'view' => Pages\ViewTheme::route('/{record}'),
        ];
    }

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.resources.theme.navigation_label');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.appearance');
    }
}
