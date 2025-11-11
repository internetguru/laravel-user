<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelCommon\Support\Helpers;
use InternetGuru\LaravelCommon\Contracts\ReCaptchaInterface;

class LoginController extends Controller
{
    public function showLogin(Request $request)
    {
        if (config('app.demo')) {
            $users = User::getDemoUsers();

            return view('ig-common::layouts.base', [
                'view' => 'login-demo',
                'prefix' => 'ig-user::',
                'props' => compact('users'),
            ]);
        }

        return view('ig-common::layouts.base', ['view' => 'login', 'prefix' => 'ig-user::']);
    }

    public function showTokenAuth()
    {
        return view('ig-common::layouts.base', ['view' => 'token-auth', 'prefix' => 'ig-user::']);
    }

    public function showRegister()
    {
        return view('ig-common::layouts.base', ['view' => 'register', 'prefix' => 'ig-user::']);
    }

    public function showRegisterEmail()
    {
        return view('ig-common::layouts.base', ['view' => 'register-email', 'prefix' => 'ig-user::']);
    }

    public function handleRegisterEmail(Request $request, ReCaptchaInterface $recaptcha)
    {
        $recaptcha->validate($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users',
        ]);

        $user = User::registerUser($request->name, $request->email);
        $user->sendTokenAuthLink();

        return redirect()->to('/')->with('success', __('ig-user::messages.register.token-auth.success')
            . Helpers::getEmailClientLink());
    }

    public function authenticate(Request $request)
    {
        User::setAuthSessions($request);

        if (! config('app.demo')) {
            Log::warning(sprintf('Invalid login.'));
            abort(400);
        }

        return $this->demoAuthenticate($request);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $lang = session('locale');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $lang);

        return redirect('/');
    }

    protected function demoAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|exists:users',
        ]);
        $user = User::where('email', $credentials['email'])->first();
        $lang = app()->getLocale();
        auth()->login($user, $request->filled('remember'));

        // $request->session()->regenerate(); // reason?

        return User::successLoginRedirect($user, $lang);
    }
}
