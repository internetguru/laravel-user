<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Give every account a ULID, the identifier users are addressed by from now on.
 *
 * Purely additive, and deliberately so: an application consuming this package
 * keeps its numeric `users.id` and merely gains a second identifier. Promoting
 * the ULID to the primary key is a decision for the application, not for every
 * consumer of the package.
 *
 * The backfill walks the table in creation order and seeds each ULID from the
 * account's own `created_at`, so the generated keys sort the way the accounts
 * were actually created instead of all carrying the timestamp of the
 * deployment. The unique index added afterwards is what proves the backfill
 * produced distinct values.
 *
 * The column stays nullable here. Making it NOT NULL means a `change()`, and
 * on SQLite that rebuilds the table through a rename, which fails while any
 * view selects from `users`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'ulid')) {
            Log::info('Skipped adding users.ulid: the column already exists');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->char('ulid', 26)->nullable()->after('id');
        });

        $backfilled = $this->backfill();

        Schema::table('users', function (Blueprint $table) {
            $table->unique('ulid');
        });

        Log::info('Added users.ulid', ['accounts_backfilled' => $backfilled]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'ulid')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });
    }

    /**
     * Fill every existing account with a ULID seeded from its creation time.
     */
    private function backfill(): int
    {
        $count = 0;

        DB::table('users')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->chunk(500, function ($users) use (&$count) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['ulid' => $this->ulidFor($user->created_at)]);

                    $count++;
                }
            });

        return $count;
    }

    /**
     * A lowercase ULID, the form `Illuminate\Database\Eloquent\Concerns\HasUlids`
     * generates, timestamped from the given moment when there is one.
     */
    private function ulidFor(?string $createdAt): string
    {
        $time = $createdAt === null ? null : Carbon::parse($createdAt);

        return strtolower((string) Str::ulid($time));
    }
};
