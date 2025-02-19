<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\SocialiteProviders\SeznamProvider;
use SocialiteProviders\Manager\OAuth2\User;
use Tests\TestCase;

class SeznamProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_token_url()
    {
        $provider = new class($this->app['request'], 'client_id', 'client_secret', 'redirect_url') extends SeznamProvider
        {
            public function getPublicTokenUrl()
            {
                return $this->getTokenUrl();
            }
        };
        $url = $provider->getPublicTokenUrl();
        $this->assertEquals('https://login.szn.cz/api/v1/oauth/token', $url);
    }

    public function test_can_map_user_to_object()
    {
        // Create a subclass to expose the protected method
        $provider = new class($this->app['request'], 'client_id', 'client_secret', 'redirect_url') extends SeznamProvider
        {
            public function mapUserToObject(array $user)
            {
                return parent::mapUserToObject($user);
            }
        };

        $userArray = [
            'oauth_user_id' => '12345',
            'username' => 'testuser',
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
        ];

        $user = $provider->mapUserToObject($userArray);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('12345', $user->getId());
        $this->assertEquals('testuser', $user->getNickname());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}
