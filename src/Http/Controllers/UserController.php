<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use InternetGuru\LaravelUser\Enums\Role;

class UserController extends Controller
{
    public function index()
    {
        return view('ig-user::base', [
            'view' => 'users.index',
            'props' => [
                'users' => User::all(),
            ],
        ]);
    }

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
            $request->validate([
                'role' => ['required', Rule::enum(Role::class)],
            ]);
            $role = Role::from($request->role);
            Gate::authorize('setRole', [$user, $role]);

            return $this->updateRole($request, $user, $role);
        }

        // unexpected request
        abort(400);
    }

    private function updateName(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $user->name = $request->name;
        $user->save();

        return back()->with('success', __('ig-user::user.update.name'));
    }

    private function updateEmail(Request $request, User $user)
    {
        $request->validate([
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email',
        ]);
        $user->email = $request->email;
        $user->save();

        return back()->with('success', __('ig-user::user.update.email'));
    }

    private function updateRole(Request $request, User $user, Role $role)
    {
        $user->role = $role;
        $user->save();

        return back()->with('success', __('ig-user::user.update.role'));
    }
}
