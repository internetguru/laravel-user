<?php

namespace InternetGuru\LaravelUser\Policies;

use InternetGuru\LaravelUser\Models\User;

class UserPolicy
{
    public function __construct()
    {
        //
    }

    /**
     * User can crud self
     * Admins can crud all
     * Managers can crud self and lower roles
     */
    public function crud(User $user, User $targetUser): bool
    {
        if ($user->id == $targetUser->id) {
            return true;
        }

        if ($user->role->level() > User::MANAGER_LEVEL) {
            return true;
        }

        if ($user->role->level() == User::MANAGER_LEVEL) {
            return $targetUser->role->level() <= User::MANAGER_LEVEL;
        }

        return false;
    }

    /**
     * Only admins and managers can view other users
     */
    public function viewAny(User $user): bool
    {
        return $user->role->level() >= User::MANAGER_LEVEL;
    }

    /**
     * Only admins and managers can administrate user
     */
    public function administrate(User $user, User $targetUser): bool
    {
        return $user->role->level() >= User::MANAGER_LEVEL;
    }

    /**
     * Admins can set any role
     * Managers can set lower roles than themselves
     * Managers can set the same role (no change)
     */
    public function setRole(User $user, User $targetUser, int $newLevel): bool
    {
        if ($user->role->level() > User::MANAGER_LEVEL) {
            return true;
        }

        if ($user->role->level() != User::MANAGER_LEVEL) {
            return false;
        }

        if ($newLevel == $targetUser->role->level()) {
            return true;
        }

        return $newLevel <= User::MANAGER_LEVEL;
    }
}
