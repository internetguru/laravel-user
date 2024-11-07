<?php

namespace InternetGuru\LaravelUser;

use Illuminate\Support\ServiceProvider;
use InternetGuru\LaravelUser\SocialiteProviders\SeznamProvider;
use Laravel\Socialite\Facades\Socialite;

class LaravelUserServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auth');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'auth');
        $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        $this->mergeConfigFrom(__DIR__ . '/../config/auth.php', 'auth');
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
