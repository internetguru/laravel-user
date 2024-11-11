<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['app.demo' => false]);
    }

    public function testShowLoginForm()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function testShowLoginFormDemo()
    {
        config(['app.demo' => true]);

        User::factory()->count(3)->create();

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');

        $users = $response->viewData('users');
        $this->assertCount(3, $users);
    }

    public function testDemoAuthenticateSuccess()
    {
        config(['app.demo' => true]);

        $user = User::factory()->withRole(Role::SPECTATOR)->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->get(route('machines.index'));
        $response->assertRedirect('/login');

        $response = $this->post(route('login.authenticate'), [
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('machines.index'));
    }

    public function testLogout()
    {
        $user = User::factory()->withRole(Role::SPECTATOR)->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

/*

    public function test_user_crud_routes()
    {
        $spectator = $this->createUser(Role::SPECTATOR);
        $operator = $this->createUser(Role::OPERATOR);
        $manager = $this->createUser(Role::MANAGER);
        $admin = $this->createUser(Role::ADMIN);

        $this->actingAs($spectator);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $spectator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $operator]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $manager]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(403);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(403);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(403);

        $this->actingAs($operator);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $spectator]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $operator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $manager]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(403);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(403);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(403);

        $this->actingAs($manager);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->put(route('users.update', ['user' => $spectator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $operator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $manager]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(200);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::MANAGER]))->assertStatus(403);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::ADMIN]))->assertStatus(403);
        $this->post(route('users.set-role', ['user' => $operator, 'role' => Role::SPECTATOR]))->assertStatus(200);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(200);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(200);

        $this->actingAs($admin);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $spectator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $operator]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $manager]))->assertStatus(200);
        $this->put(route('users.update', ['user' => $admin]))->assertStatus(200);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(200);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::MANAGER]))->assertStatus(200);
        $this->post(route('users.set-role', ['user' => $spectator, 'role' => Role::ADMIN]))->assertStatus(200);
        $this->post(route('users.set-role', ['user' => $operator, 'role' => Role::SPECTATOR]))->assertStatus(200);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(200);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(200);
    }
*/
