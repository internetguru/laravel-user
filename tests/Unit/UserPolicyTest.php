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
        $customer = User::factory()->create(['role' => Role::CUSTOMER]);

        // Admin can CRUD any user
        $this->assertTrue($this->policy->crud($admin, $admin));
        $this->assertTrue($this->policy->crud($admin, $manager));
        $this->assertTrue($this->policy->crud($admin, $operator));
        $this->assertTrue($this->policy->crud($admin, $customer));

        // Manager can CRUD self and users with roles lower than or equal to MANAGER
        $this->assertTrue($this->policy->crud($manager, $manager));
        $this->assertTrue($this->policy->crud($manager, $operator));
        $this->assertTrue($this->policy->crud($manager, $customer));
        $this->assertFalse($this->policy->crud($manager, $admin));

        // Operator can only CRUD self
        $this->assertTrue($this->policy->crud($operator, $operator));
        $this->assertFalse($this->policy->crud($operator, $manager));
        $this->assertFalse($this->policy->crud($operator, $customer));
        $this->assertFalse($this->policy->crud($operator, $admin));

        // Customer can only CRUD self
        $this->assertTrue($this->policy->crud($customer, $customer));
        $this->assertFalse($this->policy->crud($customer, $operator));
        $this->assertFalse($this->policy->crud($customer, $manager));
        $this->assertFalse($this->policy->crud($customer, $admin));
    }

    public function testViewAnyPolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $customer = User::factory()->create(['role' => Role::CUSTOMER]);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->viewAny($manager));
        $this->assertFalse($this->policy->viewAny($operator));
        $this->assertFalse($this->policy->viewAny($customer));
    }

    public function testAdministratePolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $customer = User::factory()->create(['role' => Role::CUSTOMER]);

        // Admin can administrate any user
        $this->assertTrue($this->policy->administrate($admin, $admin));
        $this->assertTrue($this->policy->administrate($admin, $manager));
        $this->assertTrue($this->policy->administrate($admin, $operator));
        $this->assertTrue($this->policy->administrate($admin, $customer));

        // Manager can administrate users
        $this->assertTrue($this->policy->administrate($manager, $manager));
        $this->assertTrue($this->policy->administrate($manager, $operator));
        $this->assertTrue($this->policy->administrate($manager, $customer));
        $this->assertTrue($this->policy->administrate($manager, $admin));

        // Operator cannot administrate
        $this->assertFalse($this->policy->administrate($operator, $operator));
        $this->assertFalse($this->policy->administrate($operator, $manager));
        $this->assertFalse($this->policy->administrate($operator, $customer));
        $this->assertFalse($this->policy->administrate($operator, $admin));
    }

    public function testSetRolePolicy()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $customer = User::factory()->create(['role' => Role::CUSTOMER]);

        // Admin can set any role
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::ADMIN->level()));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::MANAGER->level()));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::OPERATOR->level()));
        $this->assertTrue($this->policy->setRole($admin, $operator, Role::CUSTOMER->level()));

        // Manager can set roles up to MANAGER
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::OPERATOR->level()));
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::CUSTOMER->level()));
        $this->assertTrue($this->policy->setRole($manager, $operator, Role::MANAGER->level()));
        $this->assertFalse($this->policy->setRole($manager, $operator, Role::ADMIN->level()));

        // Operator cannot set roles
        $this->assertFalse($this->policy->setRole($operator, $customer, Role::OPERATOR->level()));
    }
}
