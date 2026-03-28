<?php

namespace InternetGuru\LaravelUser\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAppLocale
{
    /**
     * Handle an incoming request.
     *
     * When lang domains are configured (ig-user.lang_domains), browser detection
     * still runs on the main domain. If the detected language has a dedicated lang
     * domain, the user is redirected there. Languages without a dedicated domain
     * (e.g. da when only cs:qrpoukazy.cz is configured) are served directly.
     * Lang domains always enforce their own language.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $lang = $request->input('lang');
        $languages = config('languages');
        $langDomains = config('ig-user.lang_domains', []);
        $currentHost = $request->getHost();
        $domainToLang = array_flip($langDomains);
        $isOnLangDomain = isset($domainToLang[$currentHost]);

        // Handle explicit ?lang= parameter
        if ($lang && in_array($lang, array_keys($languages))) {
            $this->setLang($lang);
            session()->reflash();

            // Redirect to the lang domain if applicable and not already there
            if (! empty($langDomains) && isset($langDomains[$lang]) && $langDomains[$lang] !== $currentHost) {
                return redirect()->away($request->getScheme() . '://' . $langDomains[$lang] . $request->getPathInfo() . '?lang=' . $lang);
            }

            // Redirect to the main domain when switching to the main-domain language from a lang domain
            if (! empty($langDomains) && ! isset($langDomains[$lang]) && $isOnLangDomain) {
                $mainDomain = config('app.www');

                if ($mainDomain && $currentHost !== $mainDomain) {
                    return redirect()->away($request->getScheme() . '://' . $mainDomain . $request->getPathInfo() . '?lang=' . $lang);
                }
            }

            // Remove the lang parameter from the URL and redirect
            return redirect()->to($request->url());
        }

        // If on a lang domain, that domain's language takes precedence
        if ($isOnLangDomain) {
            $expectedLang = $domainToLang[$currentHost];
            $explicitLocale = $this->getExplicitLocale($languages);

            // Redirect if the user has an explicit preference for a different language
            if ($explicitLocale && $explicitLocale !== $expectedLang) {
                $uri = $request->getRequestUri();
                $separator = str_contains($uri, '?') ? '&' : '?';

                if (isset($langDomains[$explicitLocale])) {
                    return redirect()->away($request->getScheme() . '://' . $langDomains[$explicitLocale] . $uri . $separator . 'lang=' . $explicitLocale, 302);
                }

                $mainDomain = config('app.www');

                if ($mainDomain && $currentHost !== $mainDomain) {
                    return redirect()->away($request->getScheme() . '://' . $mainDomain . $uri . $separator . 'lang=' . $explicitLocale, 302);
                }
            }

            $this->setLang($expectedLang);

            return $next($request);
        }

        // Not on a lang domain: use explicit preferences only
        $explicitLocale = $this->getExplicitLocale($languages);

        if ($explicitLocale) {
            $this->setLang($explicitLocale, userSave: false);

            // Redirect to the lang domain if applicable
            if (! empty($langDomains) && isset($langDomains[$explicitLocale])) {
                $uri = $request->getRequestUri();
                $separator = str_contains($uri, '?') ? '&' : '?';

                return redirect()->away($request->getScheme() . '://' . $langDomains[$explicitLocale] . $uri . $separator . 'lang=' . $explicitLocale, 302);
            }

            return $next($request);
        }

        // No explicit preference: browser detection, redirect to lang domain if detected lang has one
        $detected = $this->detectLang($request);

        if (! in_array($detected, array_keys($languages))) {
            $detected = config('app.locale');
        }

        if (! empty($langDomains) && isset($langDomains[$detected])) {
            $uri = $request->getRequestUri();
            $separator = str_contains($uri, '?') ? '&' : '?';

            return redirect()->away($request->getScheme() . '://' . $langDomains[$detected] . $uri . $separator . 'lang=' . $detected, 302);
        }

        $this->setLang($detected);

        return $next($request);
    }

    public function setLang(string $lang, bool $userSave = true): void
    {
        session(['locale' => $lang]);
        if ($userSave && auth()->check()) {
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
            // Special case: fallback Slovak to Czech if Slovak is not supported
            if ($lang === 'sk' && ! in_array('sk', array_keys($languages)) && in_array('cs', array_keys($languages))) {
                return 'cs';
            }
            if (in_array($lang, array_keys($languages))) {
                return $lang;
            }
        }

        return (string) current(array_keys($languages));
    }

    private function getExplicitLocale(array $languages): ?string
    {
        if (auth()->check() && in_array(auth()->user()->lang, array_keys($languages))) {
            return auth()->user()->lang;
        }

        if (session()->has('locale') && in_array(session('locale'), array_keys($languages))) {
            return session('locale');
        }

        return null;
    }
}
