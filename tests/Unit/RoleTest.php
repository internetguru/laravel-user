<?php

namespace Tests\Unit;

use InternetGuru\LaravelUser\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testRoleValues()
    {
        $this->assertEquals('customer', Role::CUSTOMER->value);
        $this->assertEquals('operator', Role::OPERATOR->value);
        $this->assertEquals('auditor', Role::AUDITOR->value);
        $this->assertEquals('manager', Role::MANAGER->value);
        $this->assertEquals('admin', Role::ADMIN->value);
    }

    public function testRoleLevels()
    {
        $this->assertEquals(10, Role::CUSTOMER->level());
        $this->assertEquals(20, Role::OPERATOR->level());
        $this->assertEquals(30, Role::AUDITOR->level());
        $this->assertEquals(40, Role::MANAGER->level());
        $this->assertEquals(50, Role::ADMIN->level());
    }
}
