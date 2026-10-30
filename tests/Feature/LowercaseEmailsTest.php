<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InternetGuru\LaravelUser\Models\PinLogin;
use Tests\TestCase;

class LowercaseEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_lowercases_emails_and_leaves_conflicts()
    {
        $mixed = User::factory()->create();
        $taken = User::factory()->create(['email' => 'taken@example.com']);
        $conflicting = User::factory()->create();
        $socialite = $mixed->socialites()->create(['provider' => 'google', 'provider_id' => '1', 'name' => 'Mixed']);
        $pin = PinLogin::create(['email' => 'pin@example.com', 'pin' => '123456', 'expires_at' => now()->addHour()]);
        $pendingPin = PinLogin::create(['email' => 'pending@example.com', 'pin' => '654321', 'expires_at' => now()->addHour()]);
        $duplicatePin = PinLogin::create(['email' => 'other@example.com', 'pin' => '111111', 'expires_at' => now()->addHour()]);

        // Written past the mutators, as rows stored before them were.
        DB::table('users')->where('id', $mixed->id)->update(['email' => 'Mixed@Example.com']);
        DB::table('users')->where('id', $conflicting->id)->update(['email' => 'Taken@Example.com']);
        DB::table('socialites')->where('id', $socialite->id)->update(['email' => 'Linked@Example.com']);
        DB::table('pin_logins')->where('id', $pendingPin->id)->update(['email' => 'Pending@Example.com']);
        DB::table('pin_logins')->where('id', $duplicatePin->id)->update(['email' => 'PIN@example.com']);

        $migration = require __DIR__ . '/../../database/migrations/2026_10_30_000000_lowercase_emails.php';
        $migration->up();

        $this->assertEquals('mixed@example.com', $mixed->fresh()->email);
        $this->assertEquals('taken@example.com', $taken->fresh()->email);
        $this->assertEquals('Taken@Example.com', DB::table('users')->where('id', $conflicting->id)->value('email'));
        $this->assertEquals('linked@example.com', $socialite->fresh()->email);
        $this->assertEquals('pending@example.com', $pendingPin->fresh()->email);
        $this->assertNotNull($pin->fresh());
        $this->assertNull($duplicatePin->fresh());
    }

    public function test_models_store_email_lowercase()
    {
        $user = User::factory()->create(['email' => 'Some.One@Example.com']);
        $pin = PinLogin::create(['email' => 'Some.One@Example.com', 'pin' => '123456', 'expires_at' => now()->addHour()]);

        $this->assertEquals('some.one@example.com', DB::table('users')->where('id', $user->id)->value('email'));
        $this->assertEquals('some.one@example.com', DB::table('pin_logins')->where('id', $pin->id)->value('email'));
    }
}
