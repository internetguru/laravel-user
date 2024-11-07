<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Enums\ProviderAction;
use InternetGuru\LaravelUser\Exceptions\AuthCheckException;
use Laravel\Socialite\Facades\Socialite;

class SocialiteAuthController extends Controller
{
    private function loginRequired(): void
    {
        if (! auth()->check()) {
            throw new AuthCheckException(__('ig-user::messages.login.required'));
        }
    }

    private function loginForbidden(): void
    {
        if (auth()->check()) {
            throw new AuthCheckException(__('ig-user::messages.login.forbidden'));
        }
    }

    /**
     * Handle supported Socialite provider actions
     */
    public function handleProviderAction(string $provider, string $action, Request $request): RedirectResponse
    {
        try {
            $provider = Provider::from($provider);
            $action = ProviderAction::from($action);

            // switch the actions
            switch ($action) {
                case ProviderAction::DISCONNECT:
                    $this->loginRequired();

                    return auth()->user()->socialiteDisconnect($provider);
                case ProviderAction::LOGIN:
                case ProviderAction::REGISTER:
                    $this->loginForbidden();

                    break;
                case ProviderAction::CONNECT:
                case ProviderAction::TRANSFER:
                    $this->loginRequired();

                    break;
                default:
                    // should not happen
                    abort(404);
            }

            // save the previous url and remember option
            session([
                'auth_prev' => $request->input('prev_url', null),
                'auth_back' => url()->previous(),
                'auth_remember' => $request->input('remember') === 'true',
            ]);

            // redirect to the OAuth provider with callback url in state
            $baseUrl = URL::to("/socialite/$provider->value/$action->value/callback");
            $encodedBaseUrl = urlencode($baseUrl);

            return Socialite::driver($provider->value)->with(['state' => $encodedBaseUrl])->redirect();
        } catch (AuthCheckException $e) {
            return back()->withErrors($e->getMessage());
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('ig-user::messages.unexpected'));
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
                    $this->loginForbidden();

                    return User::socialiteLogin($provider, $providerUser);
                case ProviderAction::CONNECT:
                    $this->loginRequired();

                    return User::socialiteConnect($provider, $providerUser);
                case ProviderAction::REGISTER:
                    $this->loginForbidden();

                    return User::socialiteRegister($provider, $providerUser);
                case ProviderAction::TRANSFER:
                    $this->loginRequired();

                    return User::socialiteTransfer($provider, $providerUser);
                default:
                    // should not happen
                    abort(404);
            }
        } catch (AuthCheckException $e) {
            [, $backUrl] = User::getAuthSessions();

            return redirect()->to($backUrl)->withErrors($e->getMessage());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            [, $backUrl] = User::getAuthSessions();

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.unexpected'));
        }
    }
}
