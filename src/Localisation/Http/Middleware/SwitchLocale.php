<?php

namespace Nox\Framework\Localisation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwitchLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', $request->get('locale', config('app.locale', 'en')));

        if (! ($matchedLocale = config('localisation.'.$locale)) || ! $matchedLocale['enabled']) {
            return $next($request);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
