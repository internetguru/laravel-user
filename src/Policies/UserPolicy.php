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
     * The role list documents the permission model rather than any account's data, so every
     * signed-in user may read it. Applications restrict it by overriding this ability.
     */
    public function viewRoleList(User $user): bool
    {
        return true;
    }

    /**
     * Only admins and managers can administrate user
     */
    public function administrate(User $user, User $targetUser): bool
    {
        return $user->role->level() >= User::MANAGER_LEVEL;
    }

    /**
     * Merge two accounts into one merged group, or split one off again.
     *
     * Merging is opt-in per application: it stays off until `ig-user.merge`
     * (`AUTH_MERGE_ENABLED`) is turned on, which hides the whole section from the
     * user detail and rejects both the merge and the unmerge endpoints.
     *
     * Both subjects are always passed explicitly, because merging is mutual: authorizing only
     * one side would let a manager pull an account they may not touch into their own group.
     *
     * Requires manager level plus `crud` on both accounts. Group membership drives row
     * ownership, and owning a row generally implies the right to change it, so merging into a
     * higher-privileged account would hand the lower one access to its records. `crud` already
     * caps a manager at MANAGER_LEVEL, which keeps admin accounts out of every group a manager
     * can build. Peer managers are allowed, consistently with crud: managers may already
     * administer each other, so that is a lateral move rather than an escalation.
     */
    public function merge(User $user, User $firstUser, User $secondUser): bool
    {
        if (! config('ig-user.merge', false)) {
            return false;
        }

        if ($user->role->level() < User::MANAGER_LEVEL) {
            return false;
        }

        foreach ([$firstUser, $secondUser] as $targetUser) {
            if (! $this->crud($user, $targetUser)) {
                return false;
            }

            if ($targetUser->role->level() > $user->role->level()) {
                return false;
            }
        }

        return true;
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
