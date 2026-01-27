<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('user_course_enrollments', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }
            if (! Schema::hasColumn('user_course_enrollments', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable();
            }
            if (! Schema::hasColumn('user_course_enrollments', 'reminder_count')) {
                $table->integer('reminder_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'reminder_sent_at', 'reminder_count']);
        });
    }
};
