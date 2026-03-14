<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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

    public function authenticate(Request $request)
    {
        User::setAuthSessions($request);

        if (! config('app.demo')) {
            Log::info(sprintf('Invalid login.'));
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
        User::authenticated($user);

        // $request->session()->regenerate(); // reason?

        return User::successLoginRedirect($user, $lang);
    }
}
