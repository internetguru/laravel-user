<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An application that squashed its migrations has the table under another migration name
        if (Schema::hasTable('socialites')) {
            return;
        }

        Schema::create('socialites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->unique(['provider', 'provider_id']);
            $table->string('email');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socialites');
    }
};
