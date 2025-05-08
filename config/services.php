<?php

return [

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'icon' => 'fa-brands fa-google',
        'enabled' => env('GOOGLE_OAUTH_ENABLED', true),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'icon' => 'fa-brands fa-facebook',
        'enabled' => env('FACEBOOK_OAUTH_ENABLED', true),
    ],

    'seznam' => [
        'client_id' => env('SEZNAM_CLIENT_ID'),
        'client_secret' => env('SEZNAM_CLIENT_SECRET'),
        'redirect' => env('SEZNAM_REDIRECT_URI'),
        'icon' => 'fa-solid fa-s',
        'enabled' => env('SEZNAM_OAUTH_ENABLED', false),
    ],

];
