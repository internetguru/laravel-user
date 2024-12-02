<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (config('app.demo')) {
            $users = User::getDemoUsers();

            return view('ig-user::base', [
                'view' => 'login',
                'props' => compact('users'),
            ]);
        }

        return view('ig-user::base', ['view' => 'login']);
    }

    public function showTokenAuth()
    {
        return view('ig-user::base', ['view' => 'token-auth']);
    }

    public function showRegister()
    {
        return view('ig-user::base', ['view' => 'register']);
    }

    public function showRegisterEmail()
    {
        return view('ig-user::base', ['view' => 'register-email']);
    }

    public function handleRegisterEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users',
        ]);

        $user = User::registerUser($request->name, $request->email);

        return $user->sendTokenAuthLink();
    }

    public function authenticate(Request $request)
    {
        if (! config('app.demo')) {
            throw new \Exception('Classic login is not supported');
        }

        return $this->demoAuthenticate($request);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        // $lang = session('locale');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // $request->session()->put('locale', $lang);

        return redirect('/');
    }

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
