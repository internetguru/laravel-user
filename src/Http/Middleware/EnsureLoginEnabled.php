<?php

namespace InternetGuru\LaravelUser\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use InternetGuru\LaravelUser\Enums\ProviderAction;

class EnsureLoginEnabled
{
    /**
     * Handle an incoming request.
     *
     * Blocks login entry points when ig-user.login (AUTH_LOGIN_ENABLED) is false.
     * Routes stay registered so route('login') keeps resolving, they just 404.
     * Socialite connect/disconnect stay available — they manage identities of
     * already authenticated users and are not a way in.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->isIdentityManagement($request)) {
            return $next($request);
        }

        abort_unless(config('ig-user.login', true), 404);

        return $next($request);
    }

    private function isIdentityManagement(Request $request): bool
    {
        $action = $request->route('action');

        return in_array($action, [ProviderAction::CONNECT->value, ProviderAction::DISCONNECT->value], true);
    }
}
