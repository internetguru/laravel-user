<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelUser\Models\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

trait SocialiteAuth
{
    public function socialites(): HasMany
    {
        return $this->hasMany(Socialite::class);
    }

    public static function getBySocialiteProvider($provider, string $providerId): ?User
    {
        return Socialite::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first()
            ->user ?? null;
    }

    public static function socialiteLoginAndConnect($provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::where('email', $providerUser->email)->first();
        $connectedUser = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl, $remember] = User::getAuthSessions();

        if (! $user && ! $connectedUser) {
            Log::warning('User not found', ['provider_user' => $providerUser]);

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.identity.notfound'));
        }

        auth()->login($connectedUser ?? $user, $remember);
        User::authenticated(auth()->user());

        if (! $connectedUser) {
            User::socialiteConnect($provider, $providerUser);
        }
        return User::successLoginRedirect($user ?? $connectedUser);
    }

    public static function socialiteConnect($provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        [$prevUrl, $backUrl] = User::getAuthSessions();

        if ($user) {
            // transfer
            if ($user->email == auth()->user()->email) {
                return redirect()->to($backUrl)->withErrors(__('ig-user::messages.connect.exists.self'));
            }
            $user->socialites()
                ->where('provider', $provider)
                ->firstOrFail()
                ->update(['user_id' => auth()->id()]);
        } else {
            // connect
            $socialite = new Socialite([
                'provider' => $provider,
                'provider_id' => $providerUser->id,
                'name' => $providerUser->name,
                'email' => $providerUser->email,
            ]);
            auth()->user()
                ->socialites()
                ->save($socialite);
        }

        return redirect()->to($prevUrl)->with('success', __('ig-user::messages.connect.success'));
    }

    public static function socialiteRegister($provider, SocialiteUser $providerUser): RedirectResponse
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

        return redirect()->to('/')->with('success', __('ig-user::messages.register.success', ['name' => $user->name]));
    }

    public function socialiteDisconnect($provider): RedirectResponse
    {
        // Allow disconnect with leaving the user without any socialite
        $this->socialites()
            ->where('provider', $provider)
            ->firstOrFail()
            ->delete();

        return back()->with('success', __('ig-user::messages.disconnect.success'));
    }
}
