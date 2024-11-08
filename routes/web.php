<?php

use InternetGuru\LaravelUser\Http\Controllers\LoginController;
use InternetGuru\LaravelUser\Http\Controllers\SocialiteAuthController;
use InternetGuru\LaravelUser\Http\Controllers\TokenAuthController;
use InternetGuru\LaravelUser\Http\Controllers\UserController;

Route::controller(UserController::class)
    ->prefix('users')
    ->group(function () {
        Route::get('/{user}', 'show')
            ->middleware('can:crud,user')
            ->name('users.show');

        Route::put('/{user}', 'update')
            ->middleware('can:crud,user')
            ->name('users.update');

        Route::post('/{user}/disable', 'disable')
            ->middleware('can:enable-disable,user')
            ->name('users.disable');

        Route::post('/{user}/enable', 'enable')
            ->middleware('can:enable-disable,user')
            ->name('users.enable');

        Route::post('/{user}/set-role/{role}', 'setRole')
            ->middleware('can:setRole,user,role')
            ->name('users.set-role');
    });

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
