<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class PinLoginController extends Controller
{
    /**
     * Send PIN to the user
     */
    public function handleSend(User $user, Request $request): RedirectResponse
    {
        try {
            return $user->sendPinLogin();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('ig-user::messages.unexpected'));
        }
    }

    /**
     * Send PIN to the user based on the form email input
     */
    public function handleSendForm(Request $request): RedirectResponse
    {
        $request->validate([
            'g-recaptcha-response' => 'recaptchav3',
            'email' => 'required|email|max:255',
        ]);
        try {
            $user = User::where('email', $request->input('email'))->firstOrFail();

            return $this->handleSend($user, $request);
        } catch (ModelNotFoundException $e) {
            return back()->withInput()->withErrors(__('ig-user::messages.login.notfound'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withInput()->withErrors(__('ig-user::messages.unexpected'));
        }
    }

    /**
     * Show PIN verification form
     */
    public function showPinVerify(Request $request)
    {
        return view('ig-common::layouts.base', [
            'view' => 'pin-verify',
            'prefix' => 'ig-user::',
        ]);
    }

    /**
     * Handle PIN verification submission
     */
    public function handlePinVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        try {
            return User::pinLogin($request->input('pin'), $request->query('email'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return back()->withErrors(__('ig-user::messages.unexpected'));
        }
    }
}
}
