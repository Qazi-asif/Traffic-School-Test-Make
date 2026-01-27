<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('account_locked')->default(false)->after('email_verified_at');
            $table->text('lock_reason')->nullable()->after('account_locked');
            $table->timestamp('locked_at')->nullable()->after('lock_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_locked', 'lock_reason', 'locked_at']);
        });
    }
};
