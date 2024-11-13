<?php

namespace InternetGuru\LaravelUser\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAppLocale
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->input('lang');
        $languages = config('languages');

        // set the language if it is set in request and supported
        if ($lang && in_array($lang, array_keys($languages))) {
            $this->setLang($lang);

            return $next($request);
        }

        // use session lang if it is set and supported
        if (session()->has('locale') && in_array(session('locale'), array_keys($languages))) {
            app()->setLocale(session('locale'));

            return $next($request);
        }

        // try to detect the request language
        $lang = $this->detectLang($request);

        // fallback to the default language if the detected language is not supported
        if (! in_array($lang, array_keys($languages))) {
            $lang = config('app.locale');
        }

        // persist the detected language
        $this->setLang($lang);

        return $next($request);
    }

    public function setLang(string $lang): void
    {
        session(['locale' => $lang]);
        if (auth()->check()) {
            auth()->user()->update(['lang' => $lang]);
        }
        app()->setLocale($lang);
    }

    public function detectLang(Request $request): string
    {
        $languages = config('languages');

        foreach ($request->getLanguages() as $fullLang) {
            $lang = explode('_', $fullLang)[0];
            if (in_array($lang, array_keys($languages))) {
                return $lang;
            }
        }

        return (string) current(array_keys($languages));
    }
}
