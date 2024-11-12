<?php

function formatUserNameLink(string $name, object $user): string
{
    return sprintf('<a href="%s">%s</a>', route('users.show', $user), $name);
}
