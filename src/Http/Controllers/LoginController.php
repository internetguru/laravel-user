<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Display the login page.
     */
    public function showLogin()
    {
        if (config('auth.demo')) {
            $users = User::all()->map(
                fn ($user) => ['id' => $user->email, 'name' => $user->name]
            )->toArray();

            return view('ig-user::base', [
                'view' => 'login',
                'props' => compact('users'),
            ]);
        }

        return view('ig-user::base', ['view' => 'login']);
    }

    /**
     * Display the token authentication page.
     */
    public function showTokenAuth()
    {
        return view('ig-user::base', ['view' => 'token-auth']);
    }

    /**
     * Display the register page.
     */
    public function showRegister()
    {
        return view('ig-user::base', ['view' => 'register']);
    }

    /**
     * Display the register by email page.
     */
    public function showRegisterEmail()
    {
        return view('ig-user::base', ['view' => 'register-email']);
    }

    /**
     * Handle the register by email form submission.
     */
    public function handleRegisterEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users',
        ]);

        $user = self::registerUser($request->name, $request->email);

        return $user->sendTokenAuthLink();
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)
    {
        if (! config('auth.demo')) {
            throw new \Exception('Classic login is not supported');
        }

        return $this->demoAuthenticate($request);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        auth()->logout();

        // $lang = session('locale');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // $request->session()->put('locale', $lang);

        return redirect('/');
    }

    /**
     * Handle a demo authentication attempt.
     */
    protected function demoAuthenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|exists:users',
        ]);
        $user = auth()->getProvider()->retrieveByCredentials($credentials);
        auth()->login($user, $request->filled('remember'));

        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
