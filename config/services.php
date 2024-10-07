<?php

return [

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'icon' => 'fa-brands fa-google',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'icon' => 'fa-brands fa-google',
    ],

    'seznam' => [
        'client_id' => env('SEZNAM_CLIENT_ID'),
        'client_secret' => env('SEZNAM_CLIENT_SECRET'),
        'redirect' => env('SEZNAM_REDIRECT_URI'),
        'icon' => 'fa-solid fa-s',
    ],

];
