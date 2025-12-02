<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelCommon\CommonServiceProvider;
use InternetGuru\LaravelUser\LaravelUserServiceProvider;
use Internetguru\ModelBrowser\ModelBrowserServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // clear all users
        User::query()->delete();
    }
}
