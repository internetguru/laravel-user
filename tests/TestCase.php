<?php

namespace Tests;

use InternetGuru\LaravelSocialite\LaravelSocialiteServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            LaravelSocialiteServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Socialite' => \Laravel\Socialite\Facades\Socialite::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use MySQL testing database
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'mysql',
            'port' => 3306,
            'database' => 'testing',
            'username' => 'user',
            'password' => 'password',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }

    /**
     * Load package migrations.
     */
    protected function setUpDatabase($app)
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
    }
}
