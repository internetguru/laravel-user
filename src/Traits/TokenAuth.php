<?php

namespace InternetGuru\LaravelAuth\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use InternetGuru\LaravelAuth\Models\TokenAuth as TokenAuthModel;
use InternetGuru\LaravelAuth\Notifications\TokenAuthNotification;
use Illuminate\Support\Str;
use InternetGuru\LaravelCommon\Support\Helpers;

trait TokenAuth
{
    public function tokenAuth(): HasOne
    {
        return $this->hasOne(TokenAuthModel::class);
    }

    public function sendTokenAuthLink(): RedirectResponse
    {
        // If token already exists and newer than 5 minutes then throw
        if ($this->tokenAuth && $this->tokenAuth->updated_at->diffInMinutes() < 5) {
            return back()->withErrors(__('auth::token_auth.wait'));
        }

        $tokenAuth = $this->tokenAuth()->updateOrCreate([
            'user_id' => $this->id,
        ], [
            'token' => Str::random(32),
            'expires_at' => now()->addHour(),
        ]);

        // Send the token auth link via email
        self::sendTokenAuthNotification($tokenAuth);

        return back()->with('success', __('auth::token_auth.sent') . Helpers::getEmailClientLink());
    }

    public static function sendTokenAuthNotification(TokenAuth $tokenAuth): void
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

            return redirect()->to($backUrl)->withErrors(__('auth::token_auth.invalid'));
        }

        $tokenAuth->delete();
        auth()->login($user);
        self::authenticated($user);

        return redirect()->to($backUrl);
    }
}
