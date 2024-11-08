<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        if ($request->has('name')) {
            Gate::authorize('crud', $user);

            return $this->updateName($request, $user);
        }
        if ($request->has('email')) {
            Gate::authorize('crud', $user);

            return $this->updateEmail($request, $user);
        }
        if ($request->has('role')) {
            $role = Role::from($request->role);
            Gate::authorize('setRole', [$user, $role]);

            return $this->updateRole($user, $role);
        }

        // unexpected request
        abort(400);
    }

    public function disable(User $user)
    {
        return response('OK', 200);
    }

    public function enable(User $user)
    {
        return response('OK', 200);
    }

    private function updateName(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $user->name = $request->name;
        $user->save();

        return back()->with('success', 'Name updated');
    }

    private function updateEmail(Request $request, User $user)
    {
        $request->validate([
            'email' => 'required|string|email:rfc,dns|max:255',
        ]);
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Email updated');
    }

    private function updateRole(User $user, Role $role)
    {
        $request->validate([
            'role' => ['required', Rule::enum(Role::class)],
        ]);
        $user->role = $role;
        $user->save();

        return back()->with('success', 'Role updated');
    }
}
