<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InternetGuru\LaravelUser\Http\Controllers\SocialiteAuthController;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialiteAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleProviderActionLoginRequired()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/disconnect', 'GET');
        $response = $controller->handleProviderAction('google', 'disconnect', $request);

        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.login.required'), session('errors')->first());
    }

    public function testHandleProviderActionLoginForbidden()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/login', 'GET');
        $response = $controller->handleProviderAction('google', 'login', $request);

        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.login.forbidden'), session('errors')->first());
    }

    public function testHandleProviderActionRedirectsToProvider()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/login', 'GET');
        $request->merge(['prev_url' => 'http://example.com', 'remember' => 'true']);

        $providerMock = Mockery::mock('overload:' . Socialite::class);
        $providerMock->shouldReceive('driver->with->redirect')->andReturn(redirect('http://example.com'));

        $response = $controller->handleProviderAction('google', 'login', $request);

        $this->assertEquals(302, $response->status());
        $this->assertEquals('http://example.com', $response->getTargetUrl());
    }

    private function mockProviderUser($createUser = true)
    {
        $id = rand(1000, 9999);
        $email = 'test@example.com';
        $name = 'Test User';

        if ($createUser) {
            $user = User::factory()->create(['email' => $email]);
            $user->socialites()->create(['provider' => 'google', 'provider_id' => $id, 'email' => $user->email, 'name' => $name]);
        }

        $providerUser = Mockery::mock(SocialiteUser::class);
        $providerUser->id = $id;
        $providerUser->email = $email;
        $providerUser->name = $name;
        $providerMock = Mockery::mock('overload:' . Socialite::class);
        $providerMock->shouldReceive('driver->stateless->user')->andReturn($providerUser);

        return $user ?? null;
    }

    public function testHandleProviderCallbackLogin()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/login/callback', 'GET');

        $user = $this->mockProviderUser();
        $response = $controller->handleProviderCallback('google', 'login');

        // test success login $user
        $this->assertEquals(302, $response->status());
        $this->assertEquals('http://localhost', $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function testHandleProviderCallbackRegister()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/register/callback', 'GET');

        $user = $this->mockProviderUser();
        $response = $controller->handleProviderCallback('google', 'register');

        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.register.exists'), session('errors')->first());

        // test success register $user
        $user->socialites()->delete();
        $user->delete();
        $this->mockProviderUser(createUser: false);
        $response = $controller->handleProviderCallback('google', 'register');

        $this->assertEquals(302, $response->status());
        $this->assertEquals('http://localhost', $response->getTargetUrl());
        $this->assertTrue(Auth::check());
    }

    public function testHandleProviderCallbackConnect()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/connect/callback', 'GET');

        $providerUser = Mockery::mock(SocialiteUser::class);
        $providerUser->shouldReceive('getId')->andReturn('12345');
        $providerUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $providerMock = Mockery::mock('overload:' . Socialite::class);
        $providerMock->shouldReceive('driver->stateless->user')->andReturn($providerUser);

        $response = $controller->handleProviderCallback('google', 'connect');

        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.login.required'), session('errors')->first());
    }

    public function testHandleProviderCallbackTransfer()
    {
        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/transfer/callback', 'GET');

        $providerUser = Mockery::mock(SocialiteUser::class);
        $providerUser->shouldReceive('getId')->andReturn('12345');
        $providerUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $providerMock = Mockery::mock('overload:' . Socialite::class);
        $providerMock->shouldReceive('driver->stateless->user')->andReturn($providerUser);

        $response = $controller->handleProviderCallback('google', 'transfer');

        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.login.required'), session('errors')->first());
    }

    public function testHandleProviderCallbackUnexpectedException()
    {
        Log::shouldReceive('error')->once();

        $controller = new SocialiteAuthController;
        $request = Request::create('/socialite/google/login/callback', 'GET');

        $providerMock = Mockery::mock('overload:' . Socialite::class);
        $providerMock->shouldReceive('driver->stateless->user')->andThrow(new \Exception('Unexpected error'));

        $response = $controller->handleProviderCallback('google', 'login');

        $this->assertEquals(302, $response->status());
        $this->assertTrue(session()->has('errors'));
        $this->assertEquals(__('ig-user::messages.unexpected'), session('errors')->first());
    }
}
