<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_auths', function (Blueprint $table) {
            $table->string('pin', 6)->after('token');
            $table->dropColumn('token');
        });
    }

    public function down(): void
    {
        Schema::table('token_auths', function (Blueprint $table) {
            $table->string('token')->after('user_id');
            $table->dropColumn('pin');
        });
    }
};
