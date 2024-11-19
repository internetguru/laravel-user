<?php

namespace Tests\Unit;

use InternetGuru\LaravelUser\LaravelUserServiceProvider;
use InternetGuru\LaravelUser\SocialiteProviders\SeznamProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelUserServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelUserServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Socialite' => Socialite::class,
        ];
    }

    public function test_seznam_driver_is_registered()
    {
        $provider = Socialite::driver('seznam');

        $this->assertInstanceOf(SeznamProvider::class, $provider);
    }
}
