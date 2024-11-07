<?php

namespace InternetGuru\LaravelUser\Traits;

trait BaseAuth
{
    public static function getAuthSessions(): array
    {
        return [
            session('auth_prev', null),
            session('auth_back', '/'),
            session('auth_remember', false),
        ];
    }

    public static function authenticated(self $user): void
    {
        // Do something when the user is authenticated
    }
}
