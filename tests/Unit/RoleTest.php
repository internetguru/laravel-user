<?php

namespace Tests\Unit;

use InternetGuru\LaravelUser\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testRoleValues()
    {
        $this->assertEquals('pending', Role::PENDING->value);
        $this->assertEquals('spectator', Role::SPECTATOR->value);
        $this->assertEquals('operator', Role::OPERATOR->value);
        $this->assertEquals('manager', Role::MANAGER->value);
        $this->assertEquals('admin', Role::ADMIN->value);
    }

    public function testRoleLevels()
    {
        $this->assertEquals(1, Role::PENDING->level());
        $this->assertEquals(10, Role::SPECTATOR->level());
        $this->assertEquals(20, Role::OPERATOR->level());
        $this->assertEquals(30, Role::MANAGER->level());
        $this->assertEquals(40, Role::ADMIN->level());
    }
}
