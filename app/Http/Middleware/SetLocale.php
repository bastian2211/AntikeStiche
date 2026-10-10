<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locale aus Session lesen und für den Request setzen.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale', config('shop.default_locale', 'de'));

        if (!in_array($locale, config('shop.supported_locales', ['de', 'en']))) {
            $locale = 'de';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
