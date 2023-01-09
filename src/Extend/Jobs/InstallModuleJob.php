<?php

namespace Nox\Framework\Extend\Jobs;

use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Nox\Framework\Admin\Filament\Resources\ActivityResource;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Support\Composer;
use Spatie\Activitylog\Models\Activity;

class InstallModuleJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $name,
        private User $user
    )
    {
    }

    public function handle(Composer $composer): void
    {
        rescue(
            fn() => $this->install($composer),
            fn(Exception $e) => $this->handleError(activity()
                ->by($this->user)
                ->event('nox.module.delete')
                ->log((string)$e))
        );
    }

    private function install(Composer $composer): void
    {
        $status = $composer->require($this->name);

        $log = activity()
            ->by($this->user)
            ->event('nox.module.install')
            ->withProperty('status', $status)
            ->log($composer->getOutput()?->fetch() ?? '-');

        if ($status !== 0) {
            $this->handleError($log);
            return;
        }

        Artisan::call('package:discover');

        $this->user->notifyNow(
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.nox_update.success.title'))
                ->body(
                    __('nox::admin.notifications.nox_update.success.body',)
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.nox_update.success.actions.view_log'))
                        ->color('secondary')
                        ->url(ActivityResource::getUrl('view', ['record' => $log?->id]), true)
                        ->hidden(static function () use ($log) {
                            return $log === null;
                        }),
                ])
                ->toDatabase()
        );
    }

    private function handleError(Activity $log): void
    {
        $this->user->notifyNow(
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.nox_update.failed.title'))
                ->body(
                    __('nox::admin.notifications.module.delete.failed.body')
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.module.delete.failed.actions.view_log'))
                        ->color('secondary')
                        ->url(ActivityResource::getUrl('view', ['record' => $log?->id]), true)
                        ->hidden(static function () use ($log) {
                            return $log === null;
                        }),
                ])
                ->toDatabase()
        );
    }

    public function uniqueId(): string
    {
        return $this->name;
    }
}
