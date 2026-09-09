<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FillEmptySocialiteNamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_fills_empty_names_from_email()
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $withEmail = $user->socialites()->create([
            'provider' => 'google',
            'provider_id' => '1',
            'name' => '',
            'email' => 'linked@example.com',
        ]);
        $withoutEmail = $user->socialites()->create([
            'provider' => 'seznam',
            'provider_id' => '2',
            'name' => '',
        ]);
        $named = $user->socialites()->create([
            'provider' => 'facebook',
            'provider_id' => '3',
            'name' => 'Test User',
            'email' => 'linked@example.com',
        ]);

        $migration = require __DIR__ . '/../../database/migrations/2026_09_09_000000_fill_empty_socialite_names.php';
        $migration->up();

        $this->assertEquals('linked', $withEmail->fresh()->name);
        $this->assertEquals('owner', $withoutEmail->fresh()->name);
        $this->assertEquals('Test User', $named->fresh()->name);
    }
}
