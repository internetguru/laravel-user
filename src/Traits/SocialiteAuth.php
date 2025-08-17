<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Models\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

trait SocialiteAuth
{
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
        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl, $remember] = User::getAuthSessions();

        if (! $user) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.identity.notfound'));
        }

        auth()->login($user, $remember);
        User::authenticated(auth()->user());

        return User::successLoginRedirect($user);
    }

    public static function socialiteLoginAndConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::where('email', $providerUser->email)->first();
        [$prevUrl, $backUrl, $remember] = User::getAuthSessions();

        if (! $user) {
            Log::warning('User not found', ['provider_user' => $providerUser]);

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.identity.notfound'));
        }

        auth()->login($user, $remember);
        User::authenticated(auth()->user());

        User::socialiteConnect($provider, $providerUser);
        return User::successLoginRedirect($user);
    }

    public static function socialiteConnect(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = User::getAuthSessions();

        if ($user) {
            $message = $user->email == auth()->user()->email
                ? __('ig-user::messages.connect.exists.self')
                : __('ig-user::messages.connect.exists');

            return redirect()->to($backUrl)->withErrors($message);
        }

        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'name' => $providerUser->name,
            'email' => $providerUser->email,
        ]);
        auth()->user()
            ->socialites()
            ->save($socialite);

        return redirect()->to($prevUrl)->with('success', __('ig-user::messages.connect.success'));
    }

    public static function socialiteRegister(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = User::getAuthSessions();

        if ($user || User::where('email', $providerUser->email)->exists()) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.register.exists'));
        }

        $user = User::registerUser($providerUser->name, $providerUser->email);
        event(new Registered($user));

        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'name' => $providerUser->name,
            'email' => $providerUser->email,
        ]);
        $user->socialites()->save($socialite);

        auth()->login($user);
        User::authenticated(auth()->user());

        return redirect()->to('/')->with('success', __('ig-user::messages.register.success'));
    }

    public static function socialiteTransfer(Provider $provider, SocialiteUser $providerUser): RedirectResponse
    {
        $sourceUser = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = User::getAuthSessions();

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
