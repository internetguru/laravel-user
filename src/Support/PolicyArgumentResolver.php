<?php

namespace InternetGuru\LaravelUser\Support;

use App\Models\User;
use BackedEnum;
use InternetGuru\LaravelUser\Models\User as BaseUser;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Supplies the sample arguments the role list evaluates policy abilities with.
 *
 * Only the types the package itself knows are resolved here: accounts, the role enum and
 * scalars. Every other class resolves to null, and an ability that cannot accept null for it
 * is left out of the summary rather than guessed at — see PermissionSummary.
 *
 * Applications add their own model types by extending this class and binding the subclass:
 *
 *     $this->app->bind(PolicyArgumentResolver::class, AppPolicyArgumentResolver::class);
 */
class PolicyArgumentResolver
{
    public function resolve(ReflectionParameter $parameter, BackedEnum $role): mixed
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType) {
            return null;
        }

        $name = $type->getName();

        if (is_a($name, BaseUser::class, true)) {
            return $this->user($role);
        }

        if ($name === User::roles()) {
            return $this->lowestRole();
        }

        return match ($name) {
            'int' => 0,
            'float' => 0.0,
            'string' => '',
            'bool' => false,
            'array' => [],
            default => null,
        };
    }

    /**
     * An account of the evaluated role, never a stored one: the summary describes the role
     * model rather than the current database, and a null key keeps every relation query empty.
     *
     * Both the acting and the target account get the same role, so an ability comparing the two
     * reads as "what a role may do to its own peer".
     */
    protected function user(BackedEnum $role): BaseUser
    {
        $user = new User;
        $user->name = $role->translation();
        $user->role = $role;

        return $user;
    }

    protected function lowestRole(): BackedEnum
    {
        $roles = User::roles()::cases();
        usort($roles, fn (BackedEnum $a, BackedEnum $b): int => $a->level() <=> $b->level());

        return $roles[0];
    }
}
