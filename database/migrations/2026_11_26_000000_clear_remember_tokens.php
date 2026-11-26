<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Void the "remember me" cookies issued while a remembered sign-in could not be recalled, so a browser that kept
     * one, such as a shared computer's, is not signed in again now that it can. A current sign-in keeps its session.
     */
    public function up(): void
    {
        DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
    }

    public function down(): void
    {
        // Voided cookies are not worth restoring.
    }
};
