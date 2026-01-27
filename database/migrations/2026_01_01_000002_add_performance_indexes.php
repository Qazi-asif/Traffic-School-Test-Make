<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes for chapter queries
        Schema::table('chapters', function (Blueprint $table) {
            $table->index(['course_id', 'course_table', 'is_active', 'order_index']);
        });

        // Add indexes for progress queries
        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->index(['enrollment_id', 'is_completed']);
            $table->index(['chapter_id']);
        });

        // Add indexes for chapter breaks
        Schema::table('chapter_breaks', function (Blueprint $table) {
            $table->index(['course_id', 'course_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'course_table', 'is_active', 'order_index']);
        });

        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->dropIndex(['enrollment_id', 'is_completed']);
            $table->dropIndex(['chapter_id']);
        });

        Schema::table('chapter_breaks', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'course_type', 'is_active']);
        });
    }
};
