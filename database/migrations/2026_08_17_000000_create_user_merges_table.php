<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_merges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('merged_user_id')->constrained('users')->onDelete('cascade');
            $table->unique(['user_id', 'merged_user_id']);
            // The unique index above only covers the user_id leg. MySQL adds an index for the
            // foreign key, SQLite does not, so declare it to keep query plans identical.
            $table->index('merged_user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_merges');
    }
};
