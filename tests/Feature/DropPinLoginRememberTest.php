<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DropPinLoginRememberTest extends TestCase
{
    use RefreshDatabase;

    public function test_pin_logins_no_longer_store_the_remember_flag()
    {
        $this->assertTrue(Schema::hasTable('pin_logins'));
        $this->assertFalse(Schema::hasColumn('pin_logins', 'remember'));
    }
}
