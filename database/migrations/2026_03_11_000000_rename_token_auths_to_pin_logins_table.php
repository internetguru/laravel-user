<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('token_auths', 'pin_logins');
    }

    public function down(): void
    {
        Schema::rename('pin_logins', 'token_auths');
    }
};
