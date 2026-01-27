<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dicds_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->timestamp('attempted_at');
            $table->boolean('successful');
            $table->boolean('lockout_triggered')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dicds_login_attempts');
    }
};
