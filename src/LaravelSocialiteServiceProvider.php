<?php

namespace InternetGuru\LaravelSocialite;

use Illuminate\Support\ServiceProvider;
use InternetGuru\LaravelSocialite\SocialiteProviders\SeznamProvider;
use Laravel\Socialite\Facades\Socialite;

class LaravelSocialiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'socialite');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'socialite');
        $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        // extend socialite with seznam provider
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'seznam',
            function ($app) use ($socialite) {
                $config = $app['config']['services.seznam'];

                return $socialite->buildProvider(SeznamProvider::class, $config);
            }
        );
    }
}
