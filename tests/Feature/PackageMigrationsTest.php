<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PackageMigrationsTest extends TestCase
{
    public function test_migrate_skips_the_first_migrations_in_an_app_that_squashed_them()
    {
        DB::table('migrations')->whereIn('migration', [
            '2024_10_03_000000_create_socialites_table',
            '2024_10_15_000000_create_token_auths_table',
            '2024_11_07_000000_update_users_table',
        ])->delete();

        $this->artisan('migrate')->assertSuccessful();

        $this->assertFalse(Schema::hasTable('token_auths'));
        $this->assertTrue(Schema::hasColumn('users', 'role'));
        $this->assertFalse(Schema::hasColumn('users', 'password'));
    }
}
