<?php

use InternetGuru\LaravelUser\Enums\Role;

function getYouSuffix(object $user): string
{
    return auth()->id() === $user->id ? ' (' . __('ig-user::user.you') . ')' : '';
}

function formatUserNameLink(string $name, object $user): string
{
    return sprintf('<a href="%s">%s</a>%s', route('users.show', $user), $name, getYouSuffix($user));
}

function formatUserEmail(string $email): string
{
    return sprintf('<a href="mailto:%s">%s</a>', $email, $email);
}

function formatUserRole(Role $role): string
{
    return __('ig-user::user.roles.' . $role->value);
}
