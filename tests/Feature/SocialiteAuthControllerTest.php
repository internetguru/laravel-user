<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialiteAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectToProvider()
    {
        $response = $this->get('/socialite/google/login');
        $response->assertStatus(302);
    }

    public function testHandleProviderCallback()
    {
        // Mock the Socialite provider and user
        $providerUser = \Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $providerUser->shouldReceive('getId')->andReturn('12345');
        $providerUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $providerUser->shouldReceive('getName')->andReturn('Test User');

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Factory::class);
        $provider->shouldReceive('driver->user')->andReturn($providerUser);

        $this->app->instance(\Laravel\Socialite\Contracts\Factory::class, $provider);

        $response = $this->get('/socialite/google/login/callback');
        $response->assertStatus(302);
    }
}
