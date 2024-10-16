<?php

namespace InternetGuru\LaravelSocialite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use InternetGuru\LaravelSocialite\Enums\Provider;
use InternetGuru\LaravelSocialite\Enums\ProviderAction;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Handle supported Socialite provider actions
     */
    public function handleProviderAction(string $provider, string $action, Request $request): RedirectResponse
    {
        try {
            $provider = Provider::from($provider);
            $action = ProviderAction::from($action);

            // swich the actions
            switch ($action) {
                case ProviderAction::DISCONNECT:
                    // disconnect require logged in user
                    if (! auth()->check()) {
                        return back()->withErrors(__('socialite::messages.not_logged_in'));
                    }

                    return auth()->user()->socialiteDisconnect($provider);
                case ProviderAction::LOGIN:
                case ProviderAction::REGISTER:
                    // login and register require guest user
                    if (auth()->check()) {
                        return back()->withErrors(__('socialite::messages.already_logged_in'));
                    }
                    break;
                case ProviderAction::CONNECT:
                case ProviderAction::MERGE:
                    // connect and merge require logged in user
                    if (! auth()->check()) {
                        return back()->withErrors(__('socialite::messages.not_logged_in'));
                    }
                    break;
                default:
                    // should not happen
                    abort(404);
            }

            // save the previous url and remember option
            session([
                'socialite_prev' => $request->input('prev_url', null),
                'socialite_back' => url()->previous(),
                'socialite_remember' => $request->input('remember') === 'true',
            ]);

            // redirect to the OAuth provider with callback url in state
            $baseUrl = URL::to("/socialite/$provider->value/$action->value/callback");
            $encodedBaseUrl = urlencode($baseUrl);

            return Socialite::driver($provider->value)->with(['state' => $encodedBaseUrl])->redirect();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('socialite::messages.unexpected'));
        }
    }

    /**
     * Handle the OAuth provider callback
     */
    public function handleProviderCallback(string $provider, string $action): RedirectResponse
    {
        try {
            $provider = Provider::from($provider);
            $action = ProviderAction::from($action);

            $providerUser = Socialite::driver($provider->value)->stateless()->user();
            switch ($action) {
                case ProviderAction::LOGIN:
                    return User::socialiteLogin($provider, $providerUser);
                case ProviderAction::CONNECT:
                    return User::socialiteConnect($provider, $providerUser);
                case ProviderAction::REGISTER:
                    return User::socialiteRegister($provider, $providerUser);
                case ProviderAction::MERGE:
                    return User::socialiteMerge($provider, $providerUser);
                default:
                    // should not happen
                    abort(404);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            [, $backUrl] = User::getSocialiteSessions();

            return redirect()->to($backUrl)->withErrors(__('socialite::messages.unexpected'));
        }
    }

    /**
     * Send the token auth link to the user
     */
    public function handleTokenAuthSend(User $user, Request $request): RedirectResponse
    {
        try {
            return $user->sendTokenAuthLink();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('socialite::messages.unexpected'));
        }
    }

    /**
     * Send the token auth link to the user based on the form email input
     */
    public function handleTokenAuthSendForm(Request $request): RedirectResponse
    {
        try {
            $user = User::where('email', $request->input('email'))->firstOrFail();

            return $this->handleTokenAuthSend($user, $request);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('socialite::messages.unexpected'));
        }
    }

    /**
     * Handle the token auth callback
     */
    public function handleTokenAuthCallback(string $token): RedirectResponse
    {
        try {
            return User::tokenAuthLogin($token);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            [, $backUrl] = User::getSocialiteSessions();

            return redirect()->to($backUrl)->withErrors(__('socialite::messages.unexpected'));
        }
    }
}
