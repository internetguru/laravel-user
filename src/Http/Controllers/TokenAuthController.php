<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TokenAuthController extends Controller
{
    /**
     * Send the token auth link to the user
     */
    public function handleTokenAuthSend(User $user, Request $request): RedirectResponse
    {
        try {
            return $user->sendTokenAuthLink();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('ig-user::messages.unexpected'));
        }
    }

    /**
     * Send the token auth link to the user based on the form email input
     */
    public function handleTokenAuthSendForm(Request $request): RedirectResponse
    {
        $request->validate([
            'g-recaptcha-response' => 'recaptchav3',
            'email' => 'required|email|max:255',
        ]);
        try {
            $user = User::where('email', $request->input('email'))->firstOrFail();

            return $this->handleTokenAuthSend($user, $request);
        } catch (ModelNotFoundException $e) {
            return back()->withInput()->withErrors(__('ig-user::messages.login.notfound'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withInput()->withErrors(__('ig-user::messages.unexpected'));
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
            [, $backUrl] = User::getAuthSessions();

            return redirect()->to($backUrl)->withErrors(__('ig-user::messages.unexpected'));
        }
    }
}
