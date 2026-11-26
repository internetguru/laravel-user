<?php

namespace InternetGuru\LaravelUser;

use Exception;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InternetGuru\LaravelUser\Http\Middleware\KeepRememberedSignIn;
use InternetGuru\LaravelUser\Models\User;
use InternetGuru\LaravelUser\Policies\UserPolicy;
use InternetGuru\LaravelUser\SocialiteProviders\SeznamProvider;

class LaravelUserServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    /**
     * @return void
     */
    public function boot()
    {
        // test session.expire_on_close is set to true and lifetime is set to 2 hours
        if (config('session.expire_on_close') !== true || config('session.lifetime') != 120) {
            $message = 'Set session.expire_on_close to true and session.lifetime to 120 (2 hours) in your config/session.php file.';
            if (app()->hasDebugModeEnabled()) {
                throw new Exception($message);
            }

            Log::warning($message);
        }

        // load views, routes, translations, config
        $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        $this->mergeConfigFrom(__DIR__ . '/../config/ig-user.php', 'ig-user');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ig-user');
        Route::middleware('web')->group(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'ig-user');

        // register UserPolicy
        Gate::policy(User::class, UserPolicy::class);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // publish config, lang, views, policies
        $this->publishes([
            __DIR__ . '/../lang' => base_path('lang/vendor/ig-user'),
        ], 'ig-user:translations');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ig-user'),
        ], 'ig-user:views');
        $this->publishes([
            __DIR__ . '/Policies' => app_path('Policies'),
        ], 'ig-user:policies');

        $this->registerSanitizeTypes();
        $this->registerMiddleware();

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

    /**
     * Declare the sanitization type of every field this package's forms submit.
     *
     * internetguru/laravel-common normalizes input by what a value is, and
     * reports anything it cannot type rather than guessing. The fields below
     * are the ones only this package knows about; `email`, `name`, `phone`,
     * `pin`, `role`, `q` and `merge_user_id` are already covered by its
     * defaults, and `prev_url` by the `*_url` pattern.
     *
     * Entries the application has already set win, so publishing a config and
     * changing one of these keeps working.
     */
    private function registerSanitizeTypes(): void
    {
        $config = $this->app->make('config');
        $types = $config->get('ig-common.sanitize.types');

        if (! is_array($types)) {
            return;
        }

        $config->set('ig-common.sanitize.types', [
            // Submit buttons and checkboxes, posted as strings by the browser.
            'register' => 'flag',
            'register_check' => 'flag',
            // Still posted by sign-in pages loaded before remember-me became the default
            'remember' => 'flag',
            'remember_check' => 'flag',
            'resend' => 'flag',
            ...$types,
        ]);
    }

    private function registerMiddleware(): void
    {
        // Through the kernel: it overwrites the router's groups whenever it is resolved after boot, as in tests.
        $this->callAfterResolving(HttpKernelContract::class, function (HttpKernel $kernel) {
            $kernel->appendMiddlewareToGroup('web', KeepRememberedSignIn::class);
        });
    }
}
