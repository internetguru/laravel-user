<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Fill the identities left without a name by providers that do not share one,
     * using the local part of the identity email, or of the account email.
     */
    public function up(): void
    {
        $socialites = DB::table('socialites')
            ->leftJoin('users', 'users.id', '=', 'socialites.user_id')
            ->whereRaw("coalesce(socialites.name, '') = ''")
            ->get(['socialites.id', 'socialites.email', 'users.email as user_email']);

        foreach ($socialites as $socialite) {
            $email = $socialite->email ?: $socialite->user_email;

            if (! $email) {
                continue;
            }

            DB::table('socialites')
                ->where('id', $socialite->id)
                ->update(['name' => Str::before($email, '@')]);
        }
    }

    public function down(): void
    {
        // The original empty names are not worth restoring.
    }
};
