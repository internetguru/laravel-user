<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelUser\Models\PinLogin as PinLoginModel;

class PinLoginController extends Controller
{
    /**
     * Send PIN to the user based on the form email input
     */
    public function handleSendForm(Request $request): RedirectResponse
    {
        $request->validate([
            'g-recaptcha-response' => 'recaptchav3',
            'email' => 'required|email|max:255',
        ]);

        $remember = filter_var($request->input('remember'), FILTER_VALIDATE_BOOLEAN);
        $register = filter_var($request->input('register'), FILTER_VALIDATE_BOOLEAN);
        $resend = $request->boolean('resend');

        if (! $resend) {
            User::setAuthSessions($request);
        }

        $email = $request->input('email');

        try {
            $known = User::where('email', $email)->exists()
                || PinLoginModel::where('email', $email)->exists();

            if (! $known && ! $register) {
                return back()->withInput()->withErrors(__('ig-user::messages.login.notfound'));
            }

            // The account itself is created only once the PIN is verified
            return User::sendPinLogin($email, $remember, $register, $resend);
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
