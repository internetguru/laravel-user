<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PIN logins are short lived one time codes, so the table is simply rebuilt
     * instead of being altered in place (nullable user_id + new unique email).
     */
    public function up(): void
    {
        Schema::dropIfExists('pin_logins');

        Schema::create('pin_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->unique();
            $table->string('pin', 6);
            $table->timestamp('expires_at');
            $table->boolean('remember')->default(false);
            $table->boolean('register')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_logins');

        Schema::create('pin_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('pin', 6);
            $table->timestamp('expires_at');
            $table->boolean('remember')->default(false);
            $table->boolean('register')->default(false);
            $table->timestamps();
        });
    }
};
