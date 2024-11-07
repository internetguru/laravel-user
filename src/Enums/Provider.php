<?php

namespace InternetGuru\LaravelUser\Enums;

enum Provider: string
{
    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
    case SEZNAM = 'seznam';
}
