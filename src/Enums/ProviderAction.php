<?php

namespace InternetGuru\LaravelUser\Enums;

enum ProviderAction: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case CONNECT = 'connect';
    case DISCONNECT = 'disconnect';
}
