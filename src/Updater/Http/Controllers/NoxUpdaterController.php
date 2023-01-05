<?php

namespace Nox\Framework\Updater\Http\Controllers;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nox\Framework\Updater\Jobs\NoxUpdateJob;

class NoxUpdaterController extends Controller
{
    public function __invoke(Request $request, string $version)
    {
        abort_unless($request->hasValidSignature(), 401);

        $user = Filament::auth()->user();

        abort_if($user === null || ! $user->can('view_admin'), 401);

        NoxUpdateJob::dispatch($user, $version);

        Notification::make()
            ->success()
            ->title(__('nox::admin.notifications.nox_update.updating.title', ['version' => $version]))
            ->body(__('nox::admin.notifications.nox_update.updating.body'))
            ->send();

        return redirect()->back();
    }
}
