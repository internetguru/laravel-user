<?php

use Illuminate\Support\Facades\Route;
use InternetGuru\LaravelUser\Http\Controllers\LoginController;
use InternetGuru\LaravelUser\Http\Controllers\PinLoginController;
use InternetGuru\LaravelUser\Http\Controllers\SocialiteAuthController;
use InternetGuru\LaravelUser\Http\Controllers\UserController;

Route::controller(LoginController::class)
    ->middleware('web')
    ->group(function () {
        Route::get('/login', 'showLogin')
            ->middleware('guest')
            ->name('login');

        Route::get('/pin-login', 'showPinLogin')
            ->middleware('guest')
            ->name('pin-login');

        Route::get('/register', 'showRegister')
            ->middleware('guest')
            ->name('register');

        Route::get('/register-email', 'showRegisterEmail')
            ->middleware('guest')
            ->name('register.email');

        Route::post('/register-email', 'handleRegisterEmail')
            ->middleware('guest')
            ->name('register.email.handle');

        Route::post('/login', 'authenticate')
            ->middleware('guest')
            ->name('login.authenticate');

        Route::get('/logout', 'logout')
            ->name('logout');
    });

Route::controller(UserController::class)
    ->prefix('users')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', 'index')
            ->middleware('can:view-any,App\Models\User')
            ->name('users.index');

        Route::get('/{user}', 'show')
            ->middleware('can:crud,user')
            ->name('users.show');

        Route::post('/{user}', 'update') // check on controller level based on attribute sent
            ->name('users.update');

        Route::post('/{user}/disable', 'disable')
            ->middleware('can:enable-disable,user')
            ->name('users.disable');

        Route::post('/{user}/enable', 'enable')
            ->middleware('can:enable-disable,user')
            ->name('users.enable');
    });

Route::controller(PinLoginController::class)
    ->prefix('pin-login')
    ->middleware('web')
    ->group(function () {

        Route::post('/send', 'handleSendForm')
            ->name('pin-login.form');

        Route::get('/send/{user}', 'handleSend')
            ->name('pin-login.send');

        Route::get('/verify', 'showPinVerify')
            ->name('pin-login.verify');

        Route::post('/verify', 'handlePinVerify')
            ->middleware('throttle:5,10')
            ->name('pin-login.verify.submit');

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
