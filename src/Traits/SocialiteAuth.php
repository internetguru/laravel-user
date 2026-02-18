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

    public static function getByEmail(?string $email): ?User
    {
        // User email may be missing if permission was declined by the user
        if (! $email) {
            return null;
        }

        return User::where('email', $email)->first();
    }

    public static function socialiteLoginAndConnect($provider, SocialiteUser $providerUser): RedirectResponse
    {
        [$prevUrl, $backUrl, $remember] = User::getAuthSessions();

        // Try to find user by provider id first
        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        $foundByProviderId = (bool) $user;

        // Then try to find user by email
        if (! $user) {
            $user = User::getByEmail($providerUser->email);
        }

        // If still not found, abort
        if (! $user) {
            Log::info('User not found', ['provider_user' => $providerUser]);

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.identity.notfound', ['email' => $providerUser->email]));
        }

        auth()->login($user, $remember);
        User::authenticated($user);

        // Connect socialite if not connected yet
        if (! $foundByProviderId) {
            User::socialiteConnect($provider, $providerUser);
        }

        return User::successLoginRedirect($user);
    }

    public static function socialiteConnect($provider, SocialiteUser $providerUser): RedirectResponse
    {
        $user = User::getBySocialiteProvider($provider, $providerUser->id);

        if ($user) {
            // transfer
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

        return redirect()->to(route('users.show', ['user' => auth()->user()]))->with('success', __('ig-user::messages.connect.success'));
    }

    public static function socialiteRegister($provider, SocialiteUser $providerUser): RedirectResponse
    {
        [$prevUrl, $backUrl] = User::getAuthSessions();
        if (! $providerUser->email) {
            // Email is required for registration
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.register.noemail'));
        }

        $user = User::getBySocialiteProvider($provider, $providerUser->id);
        if ($user) {
            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.register.exists'));
        }

        // Check by email including automatic accounts
        $userByEmail = User::where('email', $providerUser->email)->first();

        if ($userByEmail) {
            if ($userByEmail->isAutomatic()) {
                // Rewrite created_by and continue registration
                $userByEmail->update([
                    'created_by' => null,
                    'name' => $providerUser->name,
                ]);
                $user = $userByEmail;
            } else {
                return redirect()->to($backUrl)->withErrors(__('ig-user::messages.register.exists'));
            }
        } else {
            $user = User::registerUser($providerUser->name, $providerUser->email);
            event(new Registered($user));
        }

        $socialite = new Socialite([
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'name' => $providerUser->name,
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
