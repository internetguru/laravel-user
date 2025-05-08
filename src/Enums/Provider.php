<?php

namespace InternetGuru\LaravelUser\Enums;

enum Provider: string
{
    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
    case SEZNAM = 'seznam';

    public static function enabledCases(): array
    {
        return array_values(
            array_filter(self::cases(), fn(self $provider): bool =>
                config('services.' . $provider->value . '.enabled', false)
            )
        );
    }
}
