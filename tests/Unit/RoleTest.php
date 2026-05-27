<?php

namespace Tests\Unit;

use InternetGuru\LaravelUser\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_role_values()
    {
        $this->assertEquals('customer', Role::CUSTOMER->value);
        $this->assertEquals('operator', Role::OPERATOR->value);
        $this->assertEquals('auditor', Role::AUDITOR->value);
        $this->assertEquals('manager', Role::MANAGER->value);
        $this->assertEquals('admin', Role::ADMIN->value);
    }

    public function test_role_levels()
    {
        $this->assertEquals(10, Role::CUSTOMER->level());
        $this->assertEquals(20, Role::OPERATOR->level());
        $this->assertEquals(30, Role::AUDITOR->level());
        $this->assertEquals(40, Role::MANAGER->level());
        $this->assertEquals(50, Role::ADMIN->level());
    }

    public function test_role_levels_are_strictly_ascending()
    {
        $levels = array_map(fn($r) => $r->level(), Role::cases());
        $sorted = $levels;
        sort($sorted);
        $this->assertEquals($sorted, $levels);
    }

    public function test_role_icons()
    {
        $this->assertEquals('fa-user', Role::CUSTOMER->icon());
        $this->assertEquals('fa-user-nurse', Role::OPERATOR->icon());
        $this->assertEquals('fa-user-shield', Role::AUDITOR->icon());
        $this->assertEquals('fa-user-tie', Role::MANAGER->icon());
        $this->assertEquals('fa-user-gear', Role::ADMIN->icon());
    }
}
