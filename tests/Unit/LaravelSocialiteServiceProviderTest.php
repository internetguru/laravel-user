<?php

namespace Tests\Unit;

use InternetGuru\LaravelSocialite\LaravelSocialiteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaravelSocialiteServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_the_service_provider()
    {
        $provider = new LaravelSocialiteServiceProvider($this->app);
        $this->assertInstanceOf(LaravelSocialiteServiceProvider::class, $provider);
    }

    public function test_loads_routes()
    {
        $provider = new LaravelSocialiteServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue($this->app->routesAreCached());
    }
}
