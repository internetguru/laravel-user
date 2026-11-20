<?php

namespace InternetGuru\LaravelUser\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Rules\PersonName;

class UserController extends Controller
{
    /**
     * @return View
     */
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

    /**
     * @return View
     */
    public function show(User $user)
    {
        return view('ig-common::layouts.base', [
            'view' => 'users.show',
            'prefix' => 'ig-user::',
            'props' => compact('user'),
        ]);
    }

    /**
     * @return RedirectResponse
     */
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
     * Search the accounts this user may be merged with.
     *
     * Backs the candidate picker on installations too large to embed the whole list in the page.
     * Both subjects of `merge` are this user: the picker itself is only rendered for someone who
     * may merge this account, and every candidate is authorized again on submit.
     *
     * @return JsonResponse
     */
    public function mergeCandidates(Request $request, User $user)
    {
        Gate::authorize('merge', [$user, $user]);

        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(
            User::mergeCandidateOptions($user, $request->string('q')->toString())
        );
    }

    /**
     * Add another account to this user's merged group.
     *
     * @return RedirectResponse
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
     *
     * @return RedirectResponse
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

    private function updateName(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', new PersonName],
        ]);
        $user->name = $request->name;
        $user->save();

        return back()->with('success', __('ig-user::user.update.name'));
    }

    private function updateEmail(Request $request, User $user): RedirectResponse
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

    private function updatePhone(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'phone' => 'nullable|string|max:50',
        ]);
        $user->phone = $request->phone ?: null;
        $user->save();

        return back()->with('success', __('ig-user::user.update.phone'));
    }

    /**
     * @param  Role  $role
     */
    private function updateRole(Request $request, User $user, $role): RedirectResponse
    {
        $user->role = $role;
        $user->save();

        return back()->with('success', __('ig-user::user.update.role'));
    }
}
