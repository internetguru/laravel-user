<?php

use InternetGuru\LaravelSocialite\Http\Controllers\SocialiteController;

Route::controller(SocialiteController::class)
    ->prefix('socialite')
    ->middleware('web')
    ->group(function () {

        Route::get('/{provider}/{action}', 'handleProviderAction')
            ->name('socialite.action');

        Route::get('/{provider}/{action}/callback', 'handleProviderCallback')
            ->name('socialite.callback');

    });

Route::controller(SocialiteController::class)
    ->prefix('token-auth')
    ->middleware('web')
    ->group(function () {

        Route::get('/send/{user}', 'handleTokenAuthSend')
            ->name('socialite.token-auth.send');

        Route::get('/callback/{token}', 'handleTokenAuthCallback')
            ->middleware('signed')
            ->name('socialite.token-auth.callback');

    });
