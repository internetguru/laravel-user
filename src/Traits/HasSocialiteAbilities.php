<?php

namespace InternetGuru\LaravelSocialite\Traits;

use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InternetGuru\LaravelSocialite\Enums\Provider;
use InternetGuru\LaravelSocialite\Models\Socialite;
use InternetGuru\LaravelSocialite\Models\TokenAuth;
use InternetGuru\LaravelSocialite\Notifications\TokenAuthNotification;
use Laravel\Socialite\Two\User as SocialiteUser;

trait HasSocialiteAbilities
{
    public function socialites(): HasMany
    {
        return $this->hasMany(Socialite::class);
    }

    public function tokenAuth(): HasOne
    {
        return $this->hasOne(TokenAuth::class);
    }

    public static function getBySocialiteProvider(Provider $provider, string $providerId): ?self
    {
        return Socialite::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first()
            ->user ?? null;
    }

    public static function socialiteLogin(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl, $remember] = self::getSocialiteSessions();

        // User not found, try to connect
        if (! $user) {
            return self::socialiteLoginAndConnect($provider, $providerUser);
        }

        // Login user
        auth()->login($user, $remember);
        self::socialiteAuthenticated($user);

        return redirect()->to($prevUrl ?? $backUrl)->with('success', __('socialite::messages.login.success'));
    }

    public static function socialiteLoginAndConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Try to find the user by email
        $user = self::where('email', $providerUser->email)->first();
        [$prevUrl, $backUrl, $remember] = self::getSocialiteSessions();

        if (! $user) {
            Log::warning('User not found', ['provider_user' => $providerUser]);

            return redirect()->to($backUrl)->withErrors(__('socialite::messages.login.notfound'));
        }

        // Login user and connect the OAuth provider
        auth()->login($user, $remember);
        self::socialiteAuthenticated($user);

        return self::socialiteConnect($provider, $providerUser);
    }

    public static function socialiteConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the id is already connected to some user
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getSocialiteSessions();

        if ($user) {
            return redirect()->to($backUrl)->withErrors(__('socialite::messages.connect.already'));
        }

        // Connect the user with the OAuth provider
        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
        ]);
        auth()->user()
            ->socialites()
            ->save($socialite);

        return redirect()->to($backUrl)->with('success', __('socialite::messages.connect.success'));
    }

    public static function socialiteRegister(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the id is already connected to some user
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getSocialiteSessions();

        if ($user) {
            return redirect()->to($backUrl)->withErrors(__('socialite::messages.register.already'));
        }

        // Register user
        $user = self::socialiteRegisterUser($providerUser);
        event(new Registered($user));

        // Connect the user with the OAuth provider
        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
        ]);
        $user->socialites()->save($socialite);

        // Login user
        auth()->login($user);
        self::socialiteAuthenticated($user);

        return redirect()->to($prevUrl)->with('success', __('socialite::messages.register.success'));
    }

    public static function socialiteRegisterUser(SocialiteUser $providerUser): self
    {
        // Create a new user
        return self::factory()->create([
            'name' => $providerUser->name,
            'email' => $providerUser->email,
        ]);
    }

    public static function getSocialiteSessions(): array
    {
        return [
            session('socialite_prev', null),
            session('socialite_back', '/'),
            session('socialite_remember', false),
        ];
    }

    public function socialiteMerge(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the merged user exists
        $mergedUser = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getSocialiteSessions();

        if (! $mergedUser) {
            return redirect()->to($backUrl)->withErrors(__('socialite::messages.merge.notfound'));
        }

        // Move the socialite from merged user to the current user
        $mergedUser->socialites()
            ->where('provider', $provider)
            ->firstOrFail()
            ->update(['user_id' => $this->id]);

        return redirect()->to($backUrl)->with('success', __('socialite::messages.merge.success'));
    }

    public function socialiteDisconnect(Provider $provider): RedirectResponse
    {
        // Allow disconnect with leaving the user without any socialite
        $this->socialites()
            ->where('provider', $provider)
            ->firstOrFail()
            ->delete();

        return back()->with('success', __('socialite::messages.disconnect.success'));
    }

    public static function socialiteAuthenticated(self $user): void
    {
        // Do something when the user is authenticated
    }

    public function sendTokenAuthLink(): RedirectResponse
    {
        [, $backUrl] = self::getSocialiteSessions();
        // If token already exists and newer than 5 minutes then throw
        if ($this->tokenAuth && $this->tokenAuth->updated_at->diffInMinutes() > -5) {
            return redirect()->to($backUrl)->withErrors(__('socialite::token_auth.wait'));
        }
        // Send the token auth link via email
        $tokenAuth = $this->tokenAuth()->updateOrCreate([
            'user_id' => $this->id,
        ], [
            'token' => Str::random(32),
            'expires_at' => now()->addHour(),
        ]);

        self::sendTokenAuthNotification($tokenAuth);

        return redirect()->to($backUrl)->with('success', __('socialite::token_auth.sent'));
    }

    public static function sendTokenAuthNotification(TokenAuth $tokenAuth): void
    {
        $tokenAuth->user->notify(new TokenAuthNotification($tokenAuth));
    }

    public static function tokenAuthLogin(string $token): RedirectResponse
    {
        $tokenAuth = TokenAuth::where('token', $token)->firstOrFail();
        $user = $tokenAuth->user;
        [, $backUrl] = self::getSocialiteSessions();

        if ($tokenAuth->expires_at->isPast()) {
            $tokenAuth->delete();

            return redirect()->to($backUrl)->withErrors(__('socialite::token_auth.invalid'));
        }

        $tokenAuth->delete();
        auth()->login($user);
        self::socialiteAuthenticated($user);

        return redirect()->to($backUrl)->with('success', __('socialite::messages.login.success'));
    }
}
