<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Http\Controllers\UserController;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define routes used in the tests
        Route::middleware(['web', 'auth'])->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::post('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/disable', [UserController::class, 'disable'])->name('users.disable');
            Route::post('/users/{user}/enable', [UserController::class, 'enable'])->name('users.enable');
        });
    }

    public function testIndexDisplaysUsers()
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

    public function testShowDisplaysUser()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'users.show');
        $response->assertViewHas('props.user', $user);
    }

    public function testUpdateNameSuccessfully()
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

    public function testUpdateEmailSuccessfully()
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

    public function testUpdateRoleSuccessfully()
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

    public function testUpdateNameValidationFails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'name' => '',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('name');
    }

    public function testUpdateEmailValidationFails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('email');
    }

    public function testUpdateRoleValidationFails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'role' => 'invalid-role',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('role');
    }

    public function testUnauthorizedUserCannotUpdateAnotherUser()
    {
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $user = User::factory()->create();

        $response = $this->actingAs($operator)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateThrowsBadRequestForUnexpectedRequest()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'unexpected' => 'data',
        ]);

        $response->assertStatus(400);
    }

    public function testGuestCannotAccessUserRoutes()
    {
        $user = User::factory()->create();

        $this->get(route('users.index'))->assertRedirect('/login');
        $this->get(route('users.show', $user))->assertRedirect('/login');
        $this->post(route('users.update', $user), ['name' => 'New Name'])->assertRedirect('/login');
        $this->post(route('users.disable', $user))->assertRedirect('/login');
        $this->post(route('users.enable', $user))->assertRedirect('/login');
    }

    public function testUserCanUpdateOwnProfile()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.name'));
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function testBasicUserCannotUpdateOthersProfile()
    {
        $user = User::factory()->withRole(Role::CUSTOMER)->create();
        $otherUser = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $otherUser), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function testManagerCanUpdateRolesWithinLimits()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);

        // Manager can promote operator to customer
        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::CUSTOMER->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.role'));
        $this->assertEquals(Role::CUSTOMER, $operator->fresh()->role);

        // Manager cannot promote operator to admin
        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::ADMIN->value,
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateEmailToExistingEmailFails()
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
}
