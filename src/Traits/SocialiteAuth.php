<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Models\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

trait SocialiteAuth
{
    use BaseAuth;

    public function socialites(): HasMany
    {
        return $this->hasMany(Socialite::class);
    }

    public static function getBySocialiteProvider(Provider $provider, string $providerId): ?User
    {
        return Socialite::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first()
            ->user ?? null;
    }

    public static function socialiteLogin(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl, $remember] = self::getAuthSessions();

        // User not found, try to connect
        if (! $user) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.login.notfound'));
        }

        // Login user
        auth()->login($user, $remember);
        self::authenticated(auth()->user());

        return redirect()->to($prevUrl ?? $backUrl);
    }

    public static function socialiteLoginAndConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Try to find the user by email
        $user = self::where('email', $providerUser->email)->first();
        [$prevUrl, $backUrl, $remember] = self::getAuthSessions();

        if (! $user) {
            Log::warning('User not found', ['provider_user' => $providerUser]);

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.login.notfound'));
        }

        // Login user and connect the OAuth provider
        auth()->login($user, $remember);
        self::authenticated(auth()->user());

        return self::socialiteConnect($provider, $providerUser);
    }

    public static function socialiteConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the id is already connected to some user
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getAuthSessions();

        if ($user) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.connect.exists'));
        }

        // Connect the user with the OAuth provider
        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'name' => $providerUser->name,
            'email' => $providerUser->email,
        ]);
        auth()->user()
            ->socialites()
            ->save($socialite);

        return redirect()->to($backUrl)->with('success', __('ig-user::messages.connect.success'));
    }

    public static function socialiteRegister(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the id is already connected to some user
        $user = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getAuthSessions();

        if ($user || self::where('email', $providerUser->email)->exists()) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.register.exists'));
        }

        // Register user
        $user = self::socialiteRegisterUser($providerUser);
        event(new Registered($user));

        // Connect the user with the OAuth provider
        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'name' => $providerUser->name,
            'email' => $providerUser->email,
        ]);
        $user->socialites()->save($socialite);

        // Login user
        auth()->login($user);
        self::authenticated(auth()->user());

        return redirect()->to($prevUrl)->with('success', __('ig-user::messages.register.success'));
    }

    public static function socialiteRegisterUser(SocialiteUser $providerUser): User
    {
        // Create a new user
        return self::factory()->create([
            'name' => $providerUser->name,
            'email' => $providerUser->email,
            'role' => Role::SPECTATOR,
        ]);
    }

    public static function socialiteTransfer(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        // Check if the source user exists
        $sourceUser = self::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = self::getAuthSessions();

        if (! $sourceUser) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.transfer.notfound'));
        }

        // Transfer the socialite from source user to the current user
        $sourceUser->socialites()
            ->where('provider', $provider)
            ->firstOrFail()
            ->update(['user_id' => auth()->id()]);

        return redirect()->to($backUrl)->with('success', __('ig-user::messages.transfer.success'));
    }

    public function socialiteDisconnect(Provider $provider): RedirectResponse
    {
        // Allow disconnect with leaving the user without any socialite
        $this->socialites()
            ->where('provider', $provider)
            ->firstOrFail()
            ->delete();

        return back()->with('success', __('ig-user::messages.disconnect.success'));
    }
}
