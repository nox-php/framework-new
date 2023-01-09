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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Nox\Framework\Admin\Filament\Resources\ActivityResource;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Support\Composer;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Spatie\Activitylog\Models\Activity;

class InstallThemeJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $name,
        private User $user
    )
    {
    }

    public function handle(ThemeRepository $themes, Composer $composer): void
    {
        rescue(
            fn() => $this->install($themes, $composer),
            fn(Exception $e) => $this->handleError(activity()
                ->by($this->user)
                ->event('nox.theme.install')
                ->log((string)$e))
        );
    }

    private function install(ThemeRepository $themes, Composer $composer): void
    {
        $status = $composer->require($this->name);

        $log = activity()
            ->by($this->user)
            ->event('nox.theme.install')
            ->withProperty('status', $status)
            ->log($composer->getOutput()?->fetch() ?? '-');

        if ($status !== 0) {
            $this->handleError($log);
            return;
        }

        $themes->clear();

        $this->updatePackageDiscovery();

        $this->user->notifyNow(
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.install.success.title', ['name' => $this->name]))
                ->body(
                    __('nox::admin.notifications.themes.install.success.body')
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.themes.install.actions.view_log'))
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
                ->title(__('nox::admin.notifications.themes.install.failed.title', ['name' => $this->name]))
                ->body(
                    __('nox::admin.notifications.themes.install.failed.body')
                )
                ->actions([
                    Action::make('view-log')
                        ->button()
                        ->label(__('nox::admin.notifications.themes.install.actions.view_log'))
                        ->color('secondary')
                        ->url(ActivityResource::getUrl('view', ['record' => $log?->id]), true)
                        ->hidden(static function () use ($log) {
                            return $log === null;
                        }),
                ])
                ->toDatabase()
        );
    }

    private function updatePackageDiscovery(): void
    {
        rescue(function () {
            $path = base_path('composer.json');

            $data = json_decode(File::get($path, true), true, 512, JSON_THROW_ON_ERROR);
            Arr::set(
                $data,
                'extra.laravel.dont-discover',
                [
                    ...Arr::get($data, 'extra.laravel.dont-discover', []),
                    $this->name
                ]
            );

            File::put(
                $path,
                json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                true
            );
        });
    }

    public function uniqueId(): string
    {
        return $this->name;
    }
}
