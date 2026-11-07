<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FillMeaninglessNamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_names_users_and_identities_from_email()
    {
        $blank = User::factory()->create(['name' => ' ', 'email' => 'jan.novak@example.com']);
        $dots = User::factory()->create(['name' => '...', 'email' => 'j.k.rowling@example.com']);
        $readable = User::factory()->create(['name' => 'Novák & syn', 'email' => 'firma@example.com']);
        $identity = $blank->socialites()->create(['provider' => 'seznam', 'provider_id' => '1', 'name' => ' ']);

        $migration = require __DIR__ . '/../../database/migrations/2026_11_07_000000_fill_meaningless_names.php';
        $migration->up();

        $this->assertEquals('Jan Novak', $blank->fresh()->name);
        $this->assertEquals('J. K. Rowling', $dots->fresh()->name);
        $this->assertEquals('Novák & syn', $readable->fresh()->name);
        $this->assertEquals('Jan Novak', $identity->fresh()->name);
    }
}
