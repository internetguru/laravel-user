<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Lowercase the stored emails, so lookups by an email match whatever case it was typed or sent in.
     */
    public function up(): void
    {
        $this->lowercase('socialites');

        $this->lowercase('users', function (object $row): void {
            Log::warning('User email left as is, its lowercase form belongs to another user', ['id' => $row->id]);
        });

        // A pending PIN is short-lived, so a duplicate is dropped rather than kept.
        $this->lowercase('pin_logins', function (object $row): void {
            DB::table('pin_logins')->where('id', $row->id)->delete();
        });
    }

    public function down(): void
    {
        // The original letter case is not worth restoring.
    }

    /**
     * Compared in PHP, as a case-insensitive MySQL collation sees no difference.
     *
     * @param  (callable(object): void)|null  $onConflict  for a unique column, called instead of the update
     */
    private function lowercase(string $table, ?callable $onConflict = null): void
    {
        $rows = DB::table($table)->whereNotNull('email')->get(['id', 'email'])
            ->filter(fn (object $row) => $row->email !== Str::lower($row->email));

        foreach ($rows as $row) {
            $email = Str::lower($row->email);

            if ($onConflict && DB::table($table)->where('id', '<>', $row->id)->where('email', $email)->exists()) {
                $onConflict($row);

                continue;
            }

            DB::table($table)->where('id', $row->id)->update(['email' => $email]);
        }
    }
};
