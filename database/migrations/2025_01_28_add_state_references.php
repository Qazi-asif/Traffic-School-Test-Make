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
        // Add course_table column to chapters
        if (Schema::hasTable('chapters') && !Schema::hasColumn('chapters', 'course_table')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->string('course_table')->default('florida_courses')->after('course_id');
                $table->index(['course_id', 'course_table']);
            });
        }

        // Add course_table column to user_course_enrollments
        if (Schema::hasTable('user_course_enrollments') && !Schema::hasColumn('user_course_enrollments', 'course_table')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                $table->string('course_table')->default('florida_courses')->after('course_id');
                $table->index(['course_id', 'course_table']);
            });
        }

        // Add state_code to users if not exists
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'state_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('state_code', 2)->default('FL')->after('email');
                $table->index('state_code');
            });
        }

        // Add target_state_table to courses if not exists
        if (Schema::hasTable('courses') && !Schema::hasColumn('courses', 'target_state_table')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('target_state_table')->default('florida_courses')->after('state_code');
                $table->index('target_state_table');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('courses', 'target_state_table')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('target_state_table');
            });
        }

        if (Schema::hasColumn('users', 'state_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('state_code');
            });
        }

        if (Schema::hasColumn('user_course_enrollments', 'course_table')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                $table->dropColumn('course_table');
            });
        }

        if (Schema::hasColumn('chapters', 'course_table')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropColumn('course_table');
            });
        }
    }
};