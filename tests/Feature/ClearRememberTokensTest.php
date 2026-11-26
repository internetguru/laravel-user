<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClearRememberTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_voids_the_remember_me_cookies_issued_before()
    {
        $user = User::factory()->create(['remember_token' => 'issued-before']);

        $migration = require __DIR__ . '/../../database/migrations/2026_11_26_000000_clear_remember_tokens.php';
        $migration->up();

        $this->assertNull(DB::table('users')->where('id', $user->id)->value('remember_token'));
    }
}
