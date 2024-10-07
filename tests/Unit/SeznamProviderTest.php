<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use InternetGuru\LaravelSocialite\SocialiteProviders\SeznamProvider;
use Mockery;
use SocialiteProviders\Manager\OAuth2\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SeznamProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_auth_url()
    {
        $provider = new SeznamProvider($this->app['request']);
        $url = $provider->getAuthUrl('state');
        $this->assertStringContainsString('https://login.szn.cz/api/v1/oauth/auth', $url);
    }

    public function test_can_get_user_by_token()
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->andReturn(new Response(200, [], json_encode([
                'oauth_user_id' => '12345',
                'username' => 'testuser',
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
            ])));

        $provider = Mockery::mock(SeznamProvider::class)->makePartial();
        $provider->shouldReceive('getHttpClient')->andReturn($mockClient);

        $user = $provider->userFromToken('token');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('12345', $user->getId());
        $this->assertEquals('testuser', $user->getNickname());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}
