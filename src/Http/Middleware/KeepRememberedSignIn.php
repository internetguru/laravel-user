<?php

namespace InternetGuru\LaravelUser\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class KeepRememberedSignIn
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = auth()->guard();
        if (! $guard instanceof SessionGuard) {
            return $next($request);
        }

        $user = $guard->user();
        if ($user) {
            if ($guard->viaRemember()) {
                $this->renew($guard, $user);
            }

            return $next($request);
        }

        if ($request->isMethod('GET') && $request->cookies->has($guard->getRecallerName())) {
            return $this->noticeEndedSignIn($guard, $request, $next);
        }

        return $next($request);
    }

    private function renew(SessionGuard $guard, Authenticatable $user): void
    {
        $value = $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|' . $guard->hashPasswordForCookie($user->getAuthPassword());
        Cookie::queue(Cookie::forever($guard->getRecallerName(), $value));
    }

    private function noticeEndedSignIn(SessionGuard $guard, Request $request, Closure $next): Response
    {
        if (! config('ig-user.login', true)) {
            Cookie::queue(Cookie::forget($guard->getRecallerName()));

            return $next($request);
        }

        $notice = __('ig-user::auth.signed-out', ['url' => route('login')]);
        session()->now('warning', [...(array) session('warning', []), $notice]);
        $response = $next($request);

        // Kept until a page shows the notice, so a redirect, an error page or a page without messages passes it on
        if (str_contains((string) $response->getContent(), $notice)) {
            Cookie::queue(Cookie::forget($guard->getRecallerName()));
        }

        return $response;
    }
}
