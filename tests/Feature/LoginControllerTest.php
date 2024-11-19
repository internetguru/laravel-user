<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        config(['app.demo' => false]);
        // add machines index route
        $this->app['router']->middleware('auth')->get('/machines', function () {
            return 'machines';
        })->name('machines.index');
    }

    public function testShowLoginForm()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('ig-user::base');
        $response->assertViewHas('view', 'login');
    }

    public function testShowLoginFormDemo()
    {
        config(['app.demo' => true]);

        User::factory()->count(3)->create();
        $users = User::getDemoUsers();

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('ig-user::base');
        $response->assertViewHas('view', 'login');
        $response->assertViewHas('props', compact('users'));
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

        $this->assertEquals(auth()->user()->id, $user->id);
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

    public function test_user_crud_routes()
    {
        $pender = User::factory()->withRole(Role::PENDING)->create();
        $spectator = User::factory()->withRole(Role::SPECTATOR)->create();
        $operator = User::factory()->withRole(Role::OPERATOR)->create();
        $manager = User::factory()->withRole(Role::MANAGER)->create();
        $admin = User::factory()->withRole(Role::ADMIN)->create();

        $this->actingAs($pender);
        $this->get(route('users.show', ['user' => $pender]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $pender]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $operator]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $manager]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $admin]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(403);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(403);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(403);

        $this->actingAs($spectator);
        $this->get(route('users.show', ['user' => $pender]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $pender]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $operator]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $manager]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $admin]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(403);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(403);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(403);

        $this->actingAs($operator);
        $this->get(route('users.show', ['user' => $pender]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(403);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $pender]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $operator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $manager]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $admin]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::PENDING]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::MANAGER]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::ADMIN]))->assertStatus(403);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(403);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(403);

        $this->actingAs($manager);
        $this->get(route('users.show', ['user' => $pender]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $pender]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $operator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $manager]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $admin]), ['name' => 'Updated Name'])->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::PENDING]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::MANAGER]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::ADMIN]))->assertStatus(403);
        $this->post(route('users.update', ['user' => $operator, 'role' => Role::SPECTATOR]))->assertStatus(302);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(200);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(200);

        $this->actingAs($admin);
        $this->get(route('users.show', ['user' => $spectator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $operator]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $manager]))->assertStatus(200);
        $this->get(route('users.show', ['user' => $admin]))->assertStatus(200);
        $this->post(route('users.update', ['user' => $pender]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $operator]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $manager]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $admin]), ['name' => 'Updated Name'])->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::PENDING]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::OPERATOR]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::MANAGER]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $spectator, 'role' => Role::ADMIN]))->assertStatus(302);
        $this->post(route('users.update', ['user' => $operator, 'role' => Role::SPECTATOR]))->assertStatus(302);
        $this->post(route('users.disable', ['user' => $spectator]))->assertStatus(200);
        $this->post(route('users.enable', ['user' => $spectator]))->assertStatus(200);
    }
}
