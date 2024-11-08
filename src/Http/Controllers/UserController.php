<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LaravelUser\Models\User;
use Illuminate\Http\Request;
use InternetGuru\LaravelUser\Enums\Role;

class UserController extends Controller
{
    public function show(User $user)
    {
        return view('ig-user::base', [
            'view' => 'users.show',
            'props' => compact('user'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        return response('OK', 200);
    }

    public function disable(User $user)
    {
        return response('OK', 200);
    }

    public function enable(User $user)
    {
        return response('OK', 200);
    }

    public function setRole(User $user, Role $role)
    {
        return response('OK', 200);
    }
}
