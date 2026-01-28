<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing enrollments to use proper course_table values
        DB::statement("UPDATE user_course_enrollments SET course_table = 'florida_courses' WHERE course_table IS NULL OR course_table = ''");
        
        // Add index for better performance on course_table lookups
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->index(['course_table', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropIndex(['course_table', 'course_id']);
        });
    }
};