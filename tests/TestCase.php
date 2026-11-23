<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelCommon\CommonServiceProvider;
use InternetGuru\LaravelRecaptchaV3\RecaptchaV3ServiceProvider;
use InternetGuru\LaravelUser\LaravelUserServiceProvider;
use Internetguru\ModelBrowser\ModelBrowserServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

use function Orchestra\Testbench\default_migration_path;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            LaravelUserServiceProvider::class,
            CommonServiceProvider::class,
            RecaptchaV3ServiceProvider::class,
            LivewireServiceProvider::class,
            SocialiteServiceProvider::class,
            ModelBrowserServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Optionally, set up your environment here
    }

    // Laravel's users table must exist before the package migrations change it
    protected function defineDatabaseMigrations()
    {
        $this->app['migrator']->path(default_migration_path());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/internetguru/laravel-common/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // clear all users
        User::query()->delete();
    }
}
