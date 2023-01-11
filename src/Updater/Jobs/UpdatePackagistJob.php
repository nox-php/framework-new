<?php

namespace Nox\Framework\Updater\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Support\Composer;

class UpdatePackagistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $packages,
        private User $user
    ) {
    }

    public function handle(Composer $composer): void
    {
        if (! $this->updatePackages($composer)) {
            return;
        }
    }

    private function updatePackages(Composer $composer): bool
    {
        dd(Arr::flatten($this->packages));
    }
}
