<?php

namespace InternetGuru\LaravelUser\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use InternetGuru\LaravelCommon\Support\Helpers;
use InternetGuru\LaravelUser\Models\TokenAuth as TokenAuthModel;
use InternetGuru\LaravelUser\Notifications\TokenAuthNotification;

trait TokenAuth
{
    use BaseAuth;

    public function tokenAuth(): HasOne
    {
        return $this->hasOne(TokenAuthModel::class);
    }

    public function sendTokenAuthLink(?string $redirectTo = null): RedirectResponse
    {
        // If token already exists and newer than 5 minutes then throw
        if ($this->tokenAuth && $this->tokenAuth->updated_at->diffInMinutes() < 5) {
            return back()->withErrors(__('ig-user::token_auth.wait'));
        }

        $tokenAuth = $this->tokenAuth()->updateOrCreate([
            'user_id' => $this->id,
        ], [
            'token' => Str::random(32),
            'expires_at' => now()->addHour(),
        ]);

        // Send the token auth link via email
        self::sendTokenAuthNotification($tokenAuth);

        return redirect()->to('/')->with('success', __('ig-user::token_auth.sent') . Helpers::getEmailClientLink());
    }

    public static function sendTokenAuthNotification(TokenAuthModel $tokenAuth): void
    {
        $tokenAuth->user->notify(new TokenAuthNotification($tokenAuth));
    }

    public static function tokenAuthLogin(string $token): RedirectResponse
    {
        $tokenAuth = TokenAuthModel::where('token', $token)->firstOrFail();
        $user = $tokenAuth->user;
        [, $backUrl] = self::getAuthSessions();

        if ($tokenAuth->expires_at->isPast()) {
            $tokenAuth->delete();

            return redirect()->to($backUrl)->withErrors(__('ig-user::token_auth.invalid'));
        }

        $tokenAuth->delete();
        auth()->login($user);
        self::authenticated(auth()->user());

        return redirect()->to($backUrl);
    }
}
