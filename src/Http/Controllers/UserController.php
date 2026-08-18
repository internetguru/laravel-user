<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('ig-common::layouts.base', [
            'view' => 'users.index',
            'prefix' => 'ig-user::',
            'props' => [
                'users' => User::all(),
            ],
        ]);
    }

    public function show(User $user)
    {
        return view('ig-common::layouts.base', [
            'view' => 'users.show',
            'prefix' => 'ig-user::',
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
        if ($request->has('phone')) {
            Gate::authorize('crud', $user);

            return $this->updatePhone($request, $user);
        }
        if ($request->has('role')) {
            $request->validate([
                'role' => ['required', Rule::enum(User::roles())],
            ]);
            $role = User::roles()::from($request->role);
            Gate::authorize('setRole', [$user, $role->level()]);

            return $this->updateRole($request, $user, $role);
        }

        // unexpected request
        abort(400);
    }

    /**
     * Add another account to this user's merged group.
     */
    public function merge(Request $request, User $user)
    {
        $mergeUser = $this->resolveMergeUser($request, $user);

        if ($user->isMergedWith($mergeUser)) {
            // Idempotent: a double submit or a back button should not read as an error
            return back()->with('success', __('ig-user::user.merge.already'));
        }

        $user->mergeWith($mergeUser);

        return back()->with('success', __('ig-user::user.merge.added', ['name' => $mergeUser->name]));
    }

    /**
     * Split an account back out of this user's merged group.
     */
    public function unmerge(Request $request, User $user)
    {
        $mergeUser = $this->resolveMergeUser($request, $user);

        if (! $user->isMergedWith($mergeUser)) {
            return back()->with('success', __('ig-user::user.merge.not-merged'));
        }

        $user->unmergeFrom($mergeUser);

        return back()->with('success', __('ig-user::user.merge.removed', ['name' => $mergeUser->name]));
    }

    /**
     * Validate and authorize the second subject of a merge operation.
     */
    private function resolveMergeUser(Request $request, User $user): User
    {
        $request->validate([
            'merge_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $mergeUser = User::findOrFail($request->integer('merge_user_id'));

        if ($mergeUser->id === $user->id) {
            abort(400);
        }

        Gate::authorize('merge', [$user, $mergeUser]);

        return $mergeUser;
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
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email,' . $user->id,
        ], [
            'email.unique' => __('ig-user::user.update.email.unique'),
        ]);
        $user->email = $request->email;
        $user->save();

        return back()->with('success', __('ig-user::user.update.email'));
    }

    private function updatePhone(Request $request, User $user)
    {
        $request->validate([
            'phone' => 'nullable|string|max:50',
        ]);
        $user->phone = $request->phone ?: null;
        $user->save();

        return back()->with('success', __('ig-user::user.update.phone'));
    }

    private function updateRole(Request $request, User $user, $role)
    {
        $user->role = $role;
        $user->save();

        return back()->with('success', __('ig-user::user.update.role'));
    }
}
