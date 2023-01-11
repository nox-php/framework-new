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
                Tables\Columns\Layout\Split::make([
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
                ]),
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\ViewColumn::make('update')
                        ->view('nox::components.filament.theme.modal.theme-update'),
                ])->columnSpan(4)
                    ->collapsible()
                    ->hidden(static fn(Theme $record): bool => $record->update === null),
            ])
            ->actions([
                Tables\Actions\Action::make('update-theme')
                    ->label(__('nox::admin.resources.theme.table.actions.update.label'))
                    ->color('success')
                    ->icon('heroicon-o-download')
                    ->requiresConfirmation()
                    ->action('updateTheme')
                    ->hidden(static fn(Theme $record): bool => $record->update === null),
                Tables\Actions\Action::make('enable-theme')
                    ->label(__('nox::admin.resources.theme.table.actions.enable'))
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action('enableTheme')
                    ->hidden(static fn(Theme $record): bool => $record->enabled || $record->update !== null),
                Tables\Actions\Action::make('disable-theme')
                    ->label(__('nox::admin.resources.theme.table.actions.disable'))
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action('disableTheme')
                    ->hidden(static fn(Theme $record): bool => !$record->enabled || $record->update !== null),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('enable-theme-grouped')
                        ->label(__('nox::admin.resources.theme.table.actions.enable'))
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action('enableTheme')
                        ->hidden(static fn(Theme $record): bool => $record->enabled || $record->update === null),
                    Tables\Actions\Action::make('disable-theme-grouped')
                        ->label(__('nox::admin.resources.theme.table.actions.disable'))
                        ->icon('heroicon-o-x')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action('disableTheme')
                        ->hidden(static fn(Theme $record): bool => !$record->enabled || $record->update === null),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->action('deleteTheme'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('update-themes')
                    ->label(__('nox::admin.resources.theme.table.bulk_actions.update'))
                    ->icon('heroicon-o-download')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action('bulkUpdateThemes'),
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
