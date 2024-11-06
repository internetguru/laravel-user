<?php

namespace InternetGuru\LaravelAuth\Enums;

enum Provider: string
{
    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
    case SEZNAM = 'seznam';
}
