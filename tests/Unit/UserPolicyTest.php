<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Policies\UserPolicy;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
    }

    public function testCrudPolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $spectator = User::factory()->create(['role' => Role::SPECTATOR]);
        $pending = User::factory()->create(['role' => Role::PENDING]);

        // Admin can CRUD any user
        $this->assertTrue($this->policy->crud($admin, $admin));
        $this->assertTrue($this->policy->crud($admin, $manager));
        $this->assertTrue($this->policy->crud($admin, $operator));
        $this->assertTrue($this->policy->crud($admin, $spectator));
        $this->assertTrue($this->policy->crud($admin, $pending));

        // Manager can CRUD self and users with roles lower than or equal to MANAGER
        $this->assertTrue($this->policy->crud($manager, $manager));
        $this->assertTrue($this->policy->crud($manager, $operator));
        $this->assertTrue($this->policy->crud($manager, $spectator));
        $this->assertTrue($this->policy->crud($manager, $pending));
        $this->assertFalse($this->policy->crud($manager, $admin));

        // Operator can only CRUD self
        $this->assertTrue($this->policy->crud($operator, $operator));
        $this->assertFalse($this->policy->crud($operator, $manager));
        $this->assertFalse($this->policy->crud($operator, $spectator));
        $this->assertFalse($this->policy->crud($operator, $pending));
        $this->assertFalse($this->policy->crud($operator, $admin));

        // Spectator can only CRUD self
        $this->assertTrue($this->policy->crud($spectator, $spectator));
        $this->assertFalse($this->policy->crud($spectator, $operator));
        $this->assertFalse($this->policy->crud($spectator, $manager));
        $this->assertFalse($this->policy->crud($spectator, $pending));
        $this->assertFalse($this->policy->crud($spectator, $admin));

        // Pending user can only CRUD self
        $this->assertTrue($this->policy->crud($pending, $pending));
        $this->assertFalse($this->policy->crud($pending, $operator));
        $this->assertFalse($this->policy->crud($pending, $spectator));
        $this->assertFalse($this->policy->crud($pending, $manager));
        $this->assertFalse($this->policy->crud($pending, $admin));
    }

    public function testViewAnyPolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $spectator = User::factory()->create(['role' => Role::SPECTATOR]);
        $pending = User::factory()->create(['role' => Role::PENDING]);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->viewAny($manager));
        $this->assertFalse($this->policy->viewAny($operator));
        $this->assertFalse($this->policy->viewAny($spectator));
        $this->assertFalse($this->policy->viewAny($pending));
    }

    public function testAdministratePolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $spectator = User::factory()->create(['role' => Role::SPECTATOR]);
        $pending = User::factory()->create(['role' => Role::PENDING]);

        // Admin can administrate any user
        $this->assertTrue($this->policy->administrate($admin, $admin));
        $this->assertTrue($this->policy->administrate($admin, $manager));
        $this->assertTrue($this->policy->administrate($admin, $operator));
        $this->assertTrue($this->policy->administrate($admin, $spectator));
        $this->assertTrue($this->policy->administrate($admin, $pending));

        // Manager can administrate users
        $this->assertTrue($this->policy->administrate($manager, $manager));
        $this->assertTrue($this->policy->administrate($manager, $operator));
        $this->assertTrue($this->policy->administrate($manager, $spectator));
        $this->assertTrue($this->policy->administrate($manager, $pending));
        $this->assertTrue($this->policy->administrate($manager, $admin));

        // Operator cannot administrate
        $this->assertFalse($this->policy->administrate($operator, $operator));
        $this->assertFalse($this->policy->administrate($operator, $manager));
        $this->assertFalse($this->policy->administrate($operator, $spectator));
        $this->assertFalse($this->policy->administrate($operator, $pending));
        $this->assertFalse($this->policy->administrate($operator, $admin));
    }

    public function testSetRolePolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $spectator = User::factory()->create(['role' => Role::SPECTATOR]);
        $pending = User::factory()->create(['role' => Role::PENDING]);

        // Admin can set any role
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::ADMIN));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::MANAGER));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::OPERATOR));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::SPECTATOR));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::PENDING));

        // Manager can set roles up to OPERATOR
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::OPERATOR));
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::SPECTATOR));
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::PENDING));
        $this->assertFalse($this->policy->setRole($manager, $operator, Role::MANAGER));
        $this->assertFalse($this->policy->setRole($manager, $operator, Role::ADMIN));

        // Operator cannot set roles
        $this->assertFalse($this->policy->setRole($operator, $spectator, Role::OPERATOR));

        // Spectator cannot set roles
        $this->assertFalse($this->policy->setRole($spectator, $pending, Role::SPECTATOR));
    }

    public function testIsNotPendingPolicy()
    {
        $spectator = User::factory()->create(['role' => Role::SPECTATOR]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $pending = User::factory()->create(['role' => Role::PENDING]);

        $this->assertTrue($this->policy->isNotPending($spectator));
        $this->assertTrue($this->policy->isNotPending($operator));
        $this->assertFalse($this->policy->isNotPending($pending));
    }
}
