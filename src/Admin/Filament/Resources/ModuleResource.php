<?php

namespace Nox\Framework\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;
use Nox\Framework\Module\Models\Module;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $slug = 'extend/modules';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('nox::admin.resources.module.label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('nox::admin.resources.module.form.inputs.name')),
                Forms\Components\TextInput::make('version')
                    ->label(__('nox::admin.resources.module.form.inputs.version'))
                    ->formatStateUsing(static fn (string $state): string => 'v'.$state),
                Forms\Components\TextInput::make('path')
                    ->label(__('nox::admin.resources.module.form.inputs.path'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(__('nox::admin.resources.module.form.inputs.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label(__('nox::admin.resources.module.table.columns.name'))
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('description')
                        ->label(__('nox::admin.resources.module.table.columns.description'))
                        ->sortable()
                        ->searchable()
                        ->limit(),
                    Tables\Columns\BadgeColumn::make('version')
                        ->label(__('nox::admin.resources.module.table.columns.version'))
                        ->sortable()
                        ->searchable(),
                ]),
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\ViewColumn::make('update')
                        ->view('nox::components.filament.module.modal.module-update'),
                ])->columnSpan(4)
                    ->collapsible()
                    ->hidden(static fn (Module $record): bool => $record->update === null),
            ])
            ->actions([
                Tables\Actions\Action::make('update-module')
                    ->label(__('nox::admin.resources.module.table.actions.update.label'))
                    ->color('success')
                    ->icon('heroicon-o-download')
                    ->requiresConfirmation()
                    ->action('updateModule')
                    ->hidden(static fn (Module $record): bool => $record->update === null),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->action('deleteModule'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('update-modules')
                    ->label(__('nox::admin.resources.module.table.bulk_actions.update'))
                    ->icon('heroicon-o-download')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action('bulkUpdateModules'),
                Tables\Actions\DeleteBulkAction::make()
                    ->action('bulkDeleteModules'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'browse' => Pages\BrowseModules::route('/browse'),
            'view' => Pages\ViewModule::route('/{record}'),
        ];
    }

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.resources.module.navigation_label');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.extend');
    }
}
