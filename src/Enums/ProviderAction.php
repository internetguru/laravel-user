<?php

namespace InternetGuru\LaravelSocialite\Enums;

enum ProviderAction: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case CONNECT = 'connect';
    case MERGE = 'merge';
    case DISCONNECT = 'disconnect';
}
