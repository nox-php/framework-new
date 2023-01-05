<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Extend\Contracts\ModuleRepository;
use Nox\Framework\Extend\Enums\ModuleStatus;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    public function enableModule(ModuleRepository $modules)
    {
        if (
            ($status = $modules->enable($this->record->name)) &&
            $status === ModuleStatus::EnabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.enable.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl('view', ['record' => $this->record]));
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.enable.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function disableModule(ModuleRepository $modules)
    {
        if (
            ($status = $modules->disable($this->record->name)) &&
            $status === ModuleStatus::DisabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.disable.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.disable.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function deleteModule(ModuleRepository $modules)
    {
        if (
            ($status = $modules->delete($this->record->name)) &&
            $status === ModuleStatus::DeleteSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.delete.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.delete.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Action::make('enable-module')
                ->label(__('nox::admin.resources.module.actions.enable'))
                ->requiresConfirmation()
                ->action('enableModule')
                ->hidden(fn (): bool => $this->record->enabled),
            Action::make('disable-module')
                ->label(__('nox::admin.resources.module.actions.disable'))
                ->action('disableModule')
                ->requiresConfirmation()
                ->color('danger')
                ->hidden(fn (): bool => ! $this->record->enabled),
            DeleteAction::make()
                ->action('deleteModule'),
        ];
    }
}
