<?php

namespace Nox\Framework\Updater\Jobs;

use Composer\InstalledVersions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Nox\Framework\Admin\Filament\Resources\ActivityResource;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\NoxServiceProvider;
use Nox\Framework\Support\Composer;
use Spatie\Activitylog\Models\Activity;

class UpdatePackagistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $packages,
        private User $user
    )
    {
        $this->packages = collect($this->packages)
            ->map(static fn(array|string $package): array => is_array($package) ? $package : [$package])
            ->all();
    }

    public function handle(Composer $composer): void
    {
        $packageNames = Arr::flatten($this->packages);
        $currentVersions = collect($packageNames)
            ->map(static fn(string $packageName): string => InstalledVersions::getVersion($packageName))
            ->all();

        $statusCode = $this->updatePackages($composer, $packageNames);

        $activityLog = activity()
            ->by($this->user)
            ->event('nox.update')
            ->withProperty('status', $statusCode)
            ->log($composer->getOutput()?->fetch() ?? '-');

        if ($statusCode !== 0) {
            $this->sendErrorNotification($activityLog);
            return;
        }

        // Since we cannot run scripts via the usual composer update process, we have to do it here
        $this->runUpdateScripts();

        // Force reload composer cache to get newly installed versions
        InstalledVersions::reload(null);

        $this->sendSuccessNotification($currentVersions, $activityLog);
    }

    private function updatePackages(Composer $composer, array $packages): int
    {
        return rescue(
            static fn(): int => $composer->update($packages),
            1
        );
    }

    private function sendErrorNotification(?Activity $activityLog): void
    {
        foreach ($this->packages as $type => $packages) {
            foreach ($packages as $package) {
                $this->user->notifyNow(
                    Notification::make()
                        ->success()
                        ->title(__('nox::admin.notifications.' . $type . '.update.failed.title', ['name' => $package]))
                        ->body(__('nox::admin.notifications.' . $type . '.update.failed.body'))
                        ->actions($this->getNotificationActions($type, $activityLog))
                        ->toDatabase()
                );
            }
        }
    }

    private function runUpdateScripts(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'laravel-assets',
            '--force' => true,
        ]);

        Artisan::call('vendor:publish', [
            '--provider' => NoxServiceProvider::class,
            '--tag' => 'assets',
            '--force' => true,
        ]);

        Artisan::call('package:discover');
    }

    private function sendSuccessNotification(array $currentVersions, ?Activity $activityLog): void
    {
        foreach ($this->packages as $type => $packages) {
            foreach ($packages as $package) {
                $this->user->notifyNow(
                    Notification::make()
                        ->success()
                        ->title(__('nox::admin.notifications.' . $type . '.update.success.title', ['name' => $package]))
                        ->body(
                            __(
                                'nox::admin.notifications.' . $type . '.update.success.body',
                                [
                                    'old_version' => $currentVersions[$package],
                                    'new_version' => InstalledVersions::getVersion($package),
                                ]
                            )
                        )
                        ->actions($this->getNotificationActions($type, $activityLog))
                        ->toDatabase()
                );
            }
        }
    }

    private function getNotificationActions(string $type, ?Activity $activityLog): array
    {
        return [
            Action::make('view-log')
                ->button()
                ->label(__('nox::admin.notifications.' . $type . '.actions.view_log'))
                ->color('secondary')
                ->url(ActivityResource::getUrl('view', ['record' => $activityLog?->id]), true)
                ->hidden(static function () use ($activityLog) {
                    return $activityLog === null;
                }),
        ];
    }
}
