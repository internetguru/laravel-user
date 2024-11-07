<?php

use InternetGuru\LaravelUser\Http\Controllers\SocialiteAuthController;
use InternetGuru\LaravelUser\Http\Controllers\TokenAuthController;
use InternetGuru\LaravelUser\Http\Controllers\LoginController;

Route::controller(LoginController::class)
    ->group(function () {
        Route::get('/login', 'showLogin')
            ->middleware('guest')
            ->name('login');

        Route::get('/token_auth', 'showTokenAuth')
            ->middleware('guest')
            ->name('token_auth');

        Route::get('/register', 'showRegister')
            ->middleware('guest')
            ->name('register');

        Route::post('/login', 'authenticate')
            ->middleware('guest')
            ->name('login.authenticate');

        Route::get('/logout', 'logout')
            ->name('logout');
    });

Route::controller(TokenAuthController::class)
    ->prefix('token-auth')
    ->middleware('web')
    ->group(function () {

        Route::post('/send', 'handleTokenAuthSendForm')
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
