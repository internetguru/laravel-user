<?php

namespace InternetGuru\LaravelUser;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InternetGuru\LaravelUser\Models\User;
use InternetGuru\LaravelUser\Policies\UserPolicy;
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
        // load views, routes, translations, config
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ig-user');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'ig-user');
        $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        $this->mergeConfigFrom(__DIR__ . '/../config/ig-user.php', 'ig-user');

        // register UserPolicy
        Gate::policy(User::class, UserPolicy::class);

        // publish config, migrations, lang, views, policies
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'ig-user:migrations');
        $this->publishes([
            __DIR__ . '/../lang' => base_path('lang/vendor/ig-user'),
        ], 'ig-user:translations');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ig-user'),
        ], 'ig-user:views');
        $this->publishes([
            __DIR__ . '/Policies' => app_path('Policies'),
        ], 'ig-user:policies');

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
