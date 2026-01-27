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
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->boolean('access_revoked')->default(false)->after('completed_at');
            $table->timestamp('access_revoked_at')->nullable()->after('access_revoked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['access_revoked', 'access_revoked_at']);
        });
    }
};
