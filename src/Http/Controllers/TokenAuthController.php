<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

            return back()->withErrors(__('auth::messages.unexpected'));
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
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(__('auth::messages.login.notfound'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('auth::messages.unexpected'));
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

            return redirect()->to($backUrl)->withErrors(__('auth::messages.unexpected'));
        }
    }
}
