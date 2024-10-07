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
