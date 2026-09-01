<?php

namespace App\Policies;

use App\Models\User;
use InternetGuru\LaravelUser\Models\User as BaseUser;
use InternetGuru\LaravelUser\Policies\UserPolicy as BaseUserPolicy;

/**
 * Fixture policy narrowing a package ability, to prove the derived class drives the summary.
 * Deliberately not named UserPolicy: that name would be auto-discovered for App\Models\User
 * and would silently narrow the ability for every other test in the suite.
 */
class AccountPolicy extends BaseUserPolicy
{
    public function viewRoleList(BaseUser $user): bool
    {
        return $user->role->level() >= User::MANAGER_LEVEL;
    }
}
