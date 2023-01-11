<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Module\Contracts\ModuleRepository;
use Nox\Framework\Module\Enums\ModuleStatus;
use Nox\Framework\Module\Models\Module;
use Nox\Framework\Updater\Jobs\CheckPackagistUpdatesJob;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    public function checkModuleUpdates(ModuleRepository $modules): void
    {
        CheckPackagistUpdatesJob::dispatch([
            'modules' => collect($modules->all())
                ->map(static fn ($module) => $module->name())
                ->values()
                ->all(),
        ]);

        Notification::make()
            ->success()
            ->title(__('nox::admin.notifications.modules.update.check.title'))
            ->body(__('nox::admin.notifications.modules.update.check.body'))
            ->send();
    }

    public function bulkUpdateModules(ModuleRepository $modules, Collection $records): void
    {
        foreach ($records as $record) {
            $this->updateModule($modules, $record);
        }
    }

    public function updateModule(ModuleRepository $modules, Module $record): void
    {
        if (
            ($status = $modules->update($record->name)) &&
            $status === ModuleStatus::UpdatePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.modules.update.pending.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.modules.update.pending.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function deleteModule(
        ModuleRepository $modules,
        Module $record
    ) {
        if (
            ($status = $modules->delete($record->name)) &&
            $status === ModuleStatus::DeletePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.modules.delete.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.modules.delete.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function bulkDeleteModules(
        ModuleRepository $modules,
        Collection $records
    ) {
        foreach ($records as $record) {
            if (
                ($status = $modules->delete($record->name)) &&
                $status === ModuleStatus::DeletePending
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::admin.notifications.modules.delete.pending.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title(__('nox::admin.notifications.modules.delete.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('check-module-updates')
                ->label(__('nox::admin.resources.module.actions.check_updates'))
                ->action('checkModuleUpdates')
                ->color('success')
                ->requiresConfirmation(),
            Action::make('browse-modules')
                ->label(__('nox::admin.resources.module.actions.browse'))
                ->url(ModuleResource::getUrl('browse')),
        ];
    }
}
