<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (! $user && $register && ! $resend) {
                $name = Str::before($request->input('email'), '@');
                $user = User::registerUser($name, $request->input('email'));
            }

            if (! $user) {
                return back()->withInput()->withErrors(__('ig-user::messages.login.notfound'));
            }

            return $user->sendPinLogin($remember, $register, $resend);
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
