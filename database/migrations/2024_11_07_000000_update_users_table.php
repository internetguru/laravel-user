<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->required();
            $table->string('lang')->nullable();
            $table->dropColumn('email_verified_at');
            $table->dropColumn('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('lang');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
        });
    }
};
