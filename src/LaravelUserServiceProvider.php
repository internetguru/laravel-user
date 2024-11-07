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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ig-user');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'ig-user');
        $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        $this->mergeConfigFrom(__DIR__ . '/../config/ig-user.php', 'ig-user');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../lang' => resource_path('lang/vendor/ig-user'),
        ], 'translations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ig-user'),
        ], 'views');

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
