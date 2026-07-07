<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_users()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'users.index');
        $response->assertViewHas('props.users', function ($viewUsers) use ($users, $admin) {
            return $viewUsers->contains($admin) && $viewUsers->intersect($users)->count() === 3;
        });
    }

    public function test_show_displays_user()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'users.show');
        $response->assertViewHas('props.user', $user);
    }

    public function test_show_displays_no_identities_message_when_user_has_no_socialites()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(__('ig-user::user.no-identities'));
    }

    public function test_update_name_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.name'));
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_update_email_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['email' => 'old@gmail.com']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'email' => 'new@gmail.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.email'));
        $this->assertEquals('new@gmail.com', $user->fresh()->email);
    }

    public function test_update_phone_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['phone' => null]);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'phone' => '+420123456789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.phone'));
        $this->assertEquals('+420123456789', $user->fresh()->phone);
    }

    public function test_update_phone_to_null()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['phone' => '+420123456789']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'phone' => '',
        ]);

        $response->assertRedirect();
        $this->assertNull($user->fresh()->phone);
    }

    public function test_update_role_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'role' => Role::OPERATOR->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.role'));
        $this->assertEquals(Role::OPERATOR, $user->fresh()->role);
    }

    public function test_update_name_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'name' => '',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('name');
    }

    public function test_update_email_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('email');
    }

    public function test_update_email_to_existing_email_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['email' => 'unique@gmail.com']);
        User::factory()->create(['email' => 'existing@gmail.com']);

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'email' => 'existing@gmail.com',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('email');
    }

    public function test_update_role_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'role' => 'invalid-role',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('role');
    }

    public function test_update_throws_bad_request_for_unexpected_request()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'unexpected' => 'data',
        ]);

        $response->assertStatus(400);
    }

    public function test_unauthorized_user_cannot_update_another_user()
    {
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $user = User::factory()->create();

        $response = $this->actingAs($operator)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_profile()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.name'));
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_basic_user_cannot_update_others_profile()
    {
        $user = User::factory()->withRole(Role::CUSTOMER)->create();
        $otherUser = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $otherUser), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_update_roles_within_limits()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);

        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::CUSTOMER->value,
        ]);

        $response->assertRedirect();
        $this->assertEquals(Role::CUSTOMER, $operator->fresh()->role);

        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::ADMIN->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_user_routes()
    {
        $user = User::factory()->create();

        $this->get(route('users.index'))->assertRedirect('/login');
        $this->get(route('users.show', $user))->assertRedirect('/login');
        $this->post(route('users.update', $user), ['name' => 'New Name'])->assertRedirect('/login');
    }
}
