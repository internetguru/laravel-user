<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The required PIN column cannot be added next to existing tokens, which are short-lived anyway
        DB::table('token_auths')->truncate();

        Schema::table('token_auths', function (Blueprint $table) {
            $table->string('pin', 6)->after('token');
            $table->dropColumn('token');
        });
    }

    public function down(): void
    {
        Schema::table('token_auths', function (Blueprint $table) {
            $table->string('token')->default('')->after('user_id');
            $table->dropColumn('pin');
        });
    }
};
