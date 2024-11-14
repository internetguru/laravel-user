<?php

namespace InternetGuru\LaravelUser\Policies;

use App\Models\User;
use InternetGuru\LaravelUser\Enums\Role;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * User can crud self
     * Admins can crud all
     * Managers can crud lower roles
     */
    public function crud(User $user, User $targetUser): bool
    {
        if ($user->id == $targetUser->id) {
            return true;
        }

        if ($user->role == Role::ADMIN) {
            return true;
        }

        if ($user->role != Role::MANAGER) {
            return false;
        }

        return $targetUser->role->level() < Role::MANAGER->level();
    }

    /**
     * Only admins and managers can view other users
     */
    public function viewAny(User $user): bool
    {
        return $user->role->level() >= Role::MANAGER->level();
    }

    /**
     * Only admins and managers can administrate user
     */
    public function administrate(User $user, User $targetUser): bool
    {
        return $user->role->level() >= Role::MANAGER->level();
    }

    /**
     * Admins can set any role
     * Managers can set lower roles than themselves, max operator
     */
    public function setRole(User $user, User $targetUser, Role $role): bool
    {
        if ($user->role == Role::ADMIN) {
            return true;
        }

        if ($user->role != Role::MANAGER) {
            return false;
        }

        if ($role->level() > Role::OPERATOR->level()) {
            return false;
        }

        return $targetUser->role->level() < Role::MANAGER->level() || $user->id == $targetUser->id;
    }

    /**
     * Admins can enable disable any user
     * Managers can enable disable users with lower roles than themselves
     */
    public function enableDisable(User $user, User $targetUser): bool
    {
        if ($user->role == Role::ADMIN) {
            return true;
        }

        if ($user->role != Role::MANAGER) {
            return false;
        }

        return $targetUser->role->level() < Role::MANAGER->level();
    }

    /**
     * User is not pending ~ is SPECTATOR or higher
     */
    public function isNotPending(User $user): bool
    {
        return $user->role->level() > Role::PENDING->level();
    }
}
