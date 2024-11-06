<?php

use InternetGuru\LaravelAuth\Http\Controllers\SocialiteAuthController;
use InternetGuru\LaravelAuth\Http\Controllers\TokenAuthController;

Route::controller(TokenAuthController::class)
    ->prefix('token-auth')
    ->middleware('web')
    ->group(function () {

        Route::post('/send', 'handleAuthSendForm')
            ->name('token-auth.form');

        Route::get('/send/{user}', 'handleTokenAuthSend')
            ->name('token-auth.send');

        Route::get('/callback/{token}', 'handleTokenAuthCallback')
            ->middleware('signed')
            ->name('token-auth.callback');

    });

Route::controller(SocialiteAuthController::class)
    ->prefix('socialite')
    ->middleware('web')
    ->group(function () {

        Route::get('/{provider}/{action}', 'handleProviderAction')
            ->name('socialite.action');

        Route::get('/{provider}/{action}/callback', 'handleProviderCallback')
            ->name('socialite.callback');

    });
