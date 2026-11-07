<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use InternetGuru\LaravelUser\Rules\PersonName;

return new class extends Migration
{
    /**
     * Name users and identities whose name holds fewer than two letters or digits (empty, " ", "..."),
     * deriving it from their email. Names that are readable but break the PersonName rule are kept.
     */
    public function up(): void
    {
        $users = DB::table('users')->get(['id', 'name', 'email'])
            ->filter(fn (object $user) => $this->isMeaningless($user->name) && $user->email);

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update(['name' => PersonName::fromEmail($user->email)]);
        }

        $socialites = DB::table('socialites')
            ->leftJoin('users', 'users.id', '=', 'socialites.user_id')
            ->get(['socialites.id', 'socialites.name', 'socialites.email', 'users.email as user_email'])
            ->filter(fn (object $socialite) => $this->isMeaningless($socialite->name) && ($socialite->email || $socialite->user_email));

        foreach ($socialites as $socialite) {
            DB::table('socialites')->where('id', $socialite->id)
                ->update(['name' => PersonName::fromEmail($socialite->email ?: $socialite->user_email)]);
        }
    }

    public function down(): void
    {
        // The original meaningless names are not worth restoring.
    }

    private function isMeaningless(?string $name): bool
    {
        return preg_match_all('/[\p{L}\p{N}]/u', (string) $name) < 2;
    }
};
