<?php

namespace Nox\Framework\Localisation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwitchLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', $request->get('locale', config('app.locale', 'en')));

        info('checking locale');

        if (! ($matchedLocale = config('localisation.'.$locale)) || ! $matchedLocale['enabled']) {
            return $next($request);
        }

        info('switching to locale: '.$locale);

        app()->setLocale($locale);

        return $next($request);
    }
}
