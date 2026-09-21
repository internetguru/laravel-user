<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move the accounts of the renamed role over to its new value. Applications
     * with a roles enum of their own hold neither value, so nothing is touched.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'auditor')
            ->update(['role' => 'supervisor']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'supervisor')
            ->update(['role' => 'auditor']);
    }
};
