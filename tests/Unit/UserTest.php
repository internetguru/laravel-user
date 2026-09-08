<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Models\User as BaseUser;
use InternetGuru\LaravelUser\Traits\PinLogin;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation()
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_role()
    {
        $user = User::factory()->create(['role' => Role::ADMIN]);

        $this->assertEquals('admin', $user->role->value);
    }

    public function test_user_traits()
    {
        $traits = class_uses(BaseUser::class);

        $this->assertContains(SocialiteAuth::class, $traits);
        $this->assertContains(PinLogin::class, $traits);
    }

    public function test_is_automatic()
    {
        $user = User::factory()->automatic()->create();

        $this->assertTrue($user->isAutomatic());
    }

    public function test_is_not_automatic_when_logged_in()
    {
        $user = User::factory()->create(); // logged_at set by factory

        $this->assertFalse($user->isAutomatic());
    }

    public function test_dynamic_is_role_methods()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isManager());

        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isAdmin());
    }

    public function test_dynamic_is_role_plus_methods()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);

        $this->assertTrue($manager->isManagerPlus());
        $this->assertTrue($manager->isOperatorPlus());
        $this->assertFalse($manager->isAdminPlus());
    }

    public function test_get_demo_users_sorted_by_role_descending()
    {
        User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Customer']);
        User::factory()->create(['role' => Role::MANAGER, 'name' => 'Manager']);

        $users = User::getDemoUsers();

        $this->assertCount(2, $users);
        $this->assertStringContainsString('Manager', $users[0]['name']);
        $this->assertStringContainsString('Customer', $users[1]['name']);
    }

    public function test_get_demo_users_excludes_automatic()
    {
        User::factory()->create(['role' => Role::MANAGER]);
        User::factory()->automatic()->create();

        $users = User::getDemoUsers();

        $this->assertCount(1, $users);
    }

    public function test_summary_excludes_users_who_never_logged_in_by_default()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->actingAs($admin);
        $automatic = User::factory()->automatic()->create();

        $this->assertNotContains($automatic->id, User::summary()->pluck('id')->all());
    }

    public function test_summary_excludes_users_who_never_logged_in_whoever_created_them()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->actingAs($admin);
        $invited = User::factory()->create([
            'logged_at' => null,
            'created_by' => $admin->id,
        ]);

        $this->assertNotContains($invited->id, User::summary()->pluck('id')->all());
    }

    public function test_summary_includes_users_who_never_logged_in_when_the_filter_is_set()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->actingAs($admin);
        $automatic = User::factory()->automatic()->create();
        $invited = User::factory()->create([
            'logged_at' => null,
            'created_by' => $admin->id,
        ]);

        session(['laravel-user-user-filters' => ['never_logged_in' => '1']]);

        $ids = User::summary()->pluck('id')->all();

        $this->assertContains($automatic->id, $ids);
        $this->assertContains($invited->id, $ids);
    }

    public function test_preferences()
    {
        $user = User::factory()->create();

        $user->setPreference('theme', 'dark');

        $this->assertEquals('dark', $user->getPreference('theme'));
        $this->assertEquals('light', $user->getPreference('missing', 'light'));
    }

    public function test_public_roles_array_excludes_admin()
    {
        $roles = User::publicRolesArray();

        $this->assertNotContains(Role::ADMIN, $roles);
        $this->assertContains(Role::MANAGER, $roles);
        $this->assertContains(Role::CUSTOMER, $roles);
    }
}
