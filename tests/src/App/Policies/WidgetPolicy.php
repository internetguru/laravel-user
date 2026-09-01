<?php

namespace App\Policies;

use App\Models\User;
use DateTimeInterface;
use RuntimeException;

/**
 * Fixture policy exercising the shapes the role list has to cope with.
 */
class WidgetPolicy
{
    public function view(User $user): bool
    {
        return $user->isOperatorPlus();
    }

    /**
     * An ability the argument resolver cannot describe: the summary leaves it out.
     */
    public function inspect(User $user, DateTimeInterface $at): bool
    {
        return $user->isOperatorPlus();
    }

    /**
     * An ability that blows up without real context: the summary leaves it out too.
     */
    public function purge(User $user): bool
    {
        throw new RuntimeException('needs a real widget');
    }
}
