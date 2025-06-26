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

            // remove the lang parameter from the url and redirect
            return redirect()->to($request->url());
        }

        // use user lang if it is set and supported
        if (auth()->check() && in_array(auth()->user()->lang, array_keys($languages))) {
            $this->setLang(auth()->user()->lang, userSave: false);

            return $next($request);
        }

        // use session lang if it is set and supported
        if (session()->has('locale') && in_array(session('locale'), array_keys($languages))) {
            $this->setLang(session('locale'), userSave: false);

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

    public function setLang(string $lang, bool $userSave = true): void
    {
        session(['locale' => $lang]);
        if ($userSave && auth()->check() && ! config('app.readonly', false)) {
            $user = auth()->user();
            $user->lang = $lang;
            $user->save();
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
