<?php

namespace Nox\Framework\Theme\Jobs;

use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nox\Framework\Admin\Filament\Resources\ActivityResource;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Support\Composer;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Spatie\Activitylog\Models\Activity;

class DeleteThemeJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $name,
        private User $user
    ) {
    }

    public function handle(ThemeRepository $themes, Composer $composer): void
    {
        rescue(
            fn () => $this->delete($themes, $composer),
            fn (Exception $e) => $this->handleError(activity()
                ->by($this->user)
                ->event('nox.theme.delete')
                ->log((string) $e))
        );
    }

    private function delete(ThemeRepository $themes, Composer $composer): void
    {
        $status = $composer->remove($this->name);

        $log = activity()
            ->by($this->user)
            ->event('nox.theme.delete')
            ->withProperty('status', $status)
            ->log($composer->getOutput()?->fetch() ?? '-');

        if ($status !== 0) {
            $this->handleError($log);

            return;
        }

        $themes->clear();

        $this->user->notifyNow(
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.delete.success.title', ['name' => $this->name]))
                ->body(
                    __('nox::admin.notifications.themes.delete.success.body')
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.themes.delete.actions.view_log'))
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
                ->title(__('nox::admin.notifications.themes.delete.success.title', ['name' => $this->name]))
                ->body(
                    __('nox::admin.notifications.themes.delete.failed.body')
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.themes.delete.actions.view_log'))
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
