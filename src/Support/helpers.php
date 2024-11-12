<?php

use InternetGuru\LaravelUser\Enums\Role;

function formatUserNameLink(string $name, object $user): string
{
    return sprintf('<a href="%s">%s</a>', route('users.show', $user), $name);
}

function formatUserRole(Role $role): string
{
    return __('ig-user::user.roles.' . $role->value);
}
