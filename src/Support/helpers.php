<?php

use InternetGuru\LaravelCommon\Contracts\HasLabel;

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

/**
 * A role as plain text, for sorting a column on it and for an exported cell.
 */
function formatUserRole($role): string
{
    return $role->translation();
}

/**
 * A role as a label, for a column read on screen. An application may put its
 * own enum in place of the packaged Role; one that says nothing about how it
 * should look is left as plain text rather than refused.
 */
function formatUserRoleLabel($role): string
{
    return $role instanceof HasLabel ? $role->toLabelHtml() : formatUserRole($role);
}
