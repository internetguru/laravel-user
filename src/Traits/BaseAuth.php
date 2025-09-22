<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Models\User as UserModel;

trait BaseAuth
{
    public const MANAGER_LEVEL = 40;

    public static function roles(): string
    {
        return Role::class;
    }

    public static function setAuthSessions(Request $request): void
    {
        session([
            'auth_prev' => $request->input('prev_url', null),
            'auth_back' => url()->previous(),
            'auth_remember' => $request->input('remember') === 'true',
        ]);
    }

    public static function getAuthSessions(): array
    {
        return [
            session('auth_prev', null),
            session('auth_back', '/'),
            session('auth_remember', false),
        ];
    }

    public static function authenticated(User|UserModel $user): void
    {
        // Do something when the user is authenticated
    }

    public static function successLoginRedirect(User|UserModel $user): RedirectResponse
    {
        [$prevUrl, $backUrl, ] = User::getAuthSessions();
        return redirect()->to($prevUrl ?? $backUrl)->with('success', __('ig-user::messages.login.success', ['name' => $user->name]));
    }

    public static function registerUser(string $name, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => static::roles()::cases()[0],
            'lang' => app()->getLocale(),
        ]);
    }
}
