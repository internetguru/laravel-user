<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pin_logins', function (Blueprint $table) {
            $table->boolean('remember')->default(false)->after('expires_at');
            $table->boolean('register')->default(false)->after('remember');
        });
    }

    public function down(): void
    {
        Schema::table('pin_logins', function (Blueprint $table) {
            $table->dropColumn(['remember', 'register']);
        });
    }
};
