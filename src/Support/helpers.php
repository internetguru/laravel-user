<?php

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

function formatUserRole($role): string
{
    return $role->translation();
}
