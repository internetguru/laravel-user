<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3;
use InternetGuru\LaravelUser\Models\PinLogin as PinLoginModel;

class PinLoginController extends Controller
{
    /**
     * Send PIN to the user based on the form email input
     */
    public function handleSendForm(Request $request): RedirectResponse
    {
        $resend = $request->boolean('resend');

        if ($resend) {
            if (! session()->has(PinLoginModel::SESSION_KEY)) {
                return redirect()->route('login');
            }
            $request->merge(['email' => session(PinLoginModel::SESSION_KEY)]);
        }

        $request->validate([
            'g-recaptcha-response' => [Rule::requiredIf(fn () => app(RecaptchaV3::class)->isEnabled()), 'recaptchav3'],
            'email' => 'required|email|max:255',
        ]);

        $register = filter_var($request->input('register'), FILTER_VALIDATE_BOOLEAN);

        if (! $resend) {
            User::setAuthSessions($request);
        }

        $email = $request->input('email');

        try {
            $known = User::where('email', $email)->exists()
                || PinLoginModel::where('email', $email)->exists();

            if (! $known && ! $register) {
                return back()->withInput()->withErrors(['email' => __('ig-user::messages.login.notfound')]);
            }

            // The account itself is created only once the PIN is verified
            return User::sendPinLogin($email, register: $register, resend: $resend);
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->withErrors(__('ig-user::messages.unexpected'));
        }
    }

    public function showPinVerify(): View|RedirectResponse
    {
        if (! session()->has(PinLoginModel::SESSION_KEY)) {
            return redirect()->route('login');
        }

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

        $email = session(PinLoginModel::SESSION_KEY);
        if (! is_string($email)) {
            return redirect()->route('login');
        }

        try {
            return User::pinLogin($request->input('pin'), $email);
        } catch (\Exception $e) {
            report($e);

            return back()->withErrors(__('ig-user::messages.unexpected'));
        }
    }
}
