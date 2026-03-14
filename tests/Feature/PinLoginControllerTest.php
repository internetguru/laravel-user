<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use InternetGuru\LaravelUser\Models\PinLogin;
use Tests\TestCase;

class PinLoginControllerTest extends TestCase
{
    public function test_send_pin_creates_pin_login_record()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post(route('pin-login.form'), [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'test@example.com']));
        $this->assertDatabaseHas('pin_logins', [
            'user_id' => $user->id,
            'remember' => false,
            'register' => false,
        ]);
        Notification::assertCount(1);
    }

    public function test_send_pin_with_remember_and_register()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post(route('pin-login.form'), [
            'email' => 'test@example.com',
            'remember' => 'true',
            'register' => 'true',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'test@example.com']));
        $this->assertDatabaseHas('pin_logins', [
            'user_id' => $user->id,
            'remember' => true,
            'register' => true,
        ]);
    }

    public function test_send_pin_updates_existing_record()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->travel(-2)->minutes();
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '111111',
            'expires_at' => now()->subMinutes(5),
            'remember' => false,
            'register' => false,
        ]);
        $this->travelBack();

        $response = $this->post(route('pin-login.form'), [
            'email' => 'test@example.com',
            'remember' => 'true',
            'register' => 'true',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'test@example.com']));
        $this->assertDatabaseCount('pin_logins', 1);
        $this->assertDatabaseHas('pin_logins', [
            'user_id' => $user->id,
            'remember' => true,
            'register' => true,
        ]);
        $this->assertDatabaseMissing('pin_logins', [
            'pin' => '111111',
        ]);
    }

    public function test_resend_preserves_remember_and_register()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->travel(-2)->minutes();
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '111111',
            'expires_at' => now()->subMinutes(5),
            'remember' => true,
            'register' => true,
        ]);
        $this->travelBack();

        $response = $this->post(route('pin-login.form'), [
            'email' => 'test@example.com',
            'resend' => '1',
            'remember' => 'false',
            'register' => 'false',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'test@example.com']));
        $this->assertDatabaseHas('pin_logins', [
            'user_id' => $user->id,
            'remember' => true,
            'register' => true,
        ]);
    }

    public function test_send_pin_email_not_found()
    {
        $response = $this->post(route('pin-login.form'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_send_pin_with_register_creates_new_user()
    {
        Notification::fake();

        $response = $this->post(route('pin-login.form'), [
            'email' => 'newuser@example.com',
            'register' => 'true',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'newuser@example.com']));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
        $this->assertDatabaseHas('pin_logins', [
            'register' => true,
        ]);
    }

    public function test_send_pin_without_register_does_not_create_user()
    {
        $response = $this->post(route('pin-login.form'), [
            'email' => 'newuser@example.com',
            'register' => 'false',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_verify_pin_success()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '123456',
            'expires_at' => now()->addMinutes(10),
            'remember' => false,
            'register' => false,
        ]);

        $response = $this->post(route('pin-login.verify.submit', ['email' => 'test@example.com']), [
            'pin' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('pin_logins', ['user_id' => $user->id]);
    }

    public function test_verify_pin_with_remember()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '123456',
            'expires_at' => now()->addMinutes(10),
            'remember' => true,
            'register' => false,
        ]);

        $response = $this->post(route('pin-login.verify.submit', ['email' => 'test@example.com']), [
            'pin' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        // Check remember cookie is set
        $this->assertNotNull(auth()->user()->getRememberToken());
    }

    public function test_verify_pin_expired()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '123456',
            'expires_at' => now()->subMinutes(1),
            'remember' => false,
            'register' => false,
        ]);

        $response = $this->post(route('pin-login.verify.submit', ['email' => 'test@example.com']), [
            'pin' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_verify_pin_invalid()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '123456',
            'expires_at' => now()->addMinutes(10),
            'remember' => false,
            'register' => false,
        ]);

        $response = $this->post(route('pin-login.verify.submit', ['email' => 'test@example.com']), [
            'pin' => '654321',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_show_pin_verify()
    {
        $response = $this->get(route('pin-login.verify', ['email' => 'test@example.com']));
        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'pin-verify');
    }

    public function test_register_redirects_to_login()
    {
        $response = $this->get(route('register'));
        $response->assertRedirect(route('login'));
    }

    public function test_pin_login_redirects_to_login()
    {
        $response = $this->get(route('pin-login'));
        $response->assertRedirect(route('login'));
    }

    public function test_throttle_pin_send()
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        PinLogin::create([
            'user_id' => $user->id,
            'pin' => '111111',
            'expires_at' => now()->addMinutes(10),
            'remember' => false,
            'register' => false,
        ]);

        $response = $this->post(route('pin-login.form'), [
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('pin-login.verify', ['email' => 'test@example.com']));
        $response->assertSessionHasErrors();
        Notification::assertNothingSent();
    }
}
