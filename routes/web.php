<?php

use InternetGuru\LaravelSocialite\Http\Controllers\SocialiteController;

Route::controller(SocialiteController::class)
    ->prefix('socialite')
    ->middleware('web')
    ->group(function () {

        Route::get('/token-auth/{token}', 'handleTokenAuthCallback')
            ->middleware('signed')
            ->name('socialite.token-auth.callback');

        Route::get('/token-auth/{user}', 'handleTokenAuthSend')
            ->name('socialite.token-auth.send');

        Route::get('/{provider}/{action}', 'handleProviderAction')
            ->name('socialite.action');

        Route::get('/{provider}/{action}/callback', 'handleProviderCallback')
            ->name('socialite.callback');

    });
