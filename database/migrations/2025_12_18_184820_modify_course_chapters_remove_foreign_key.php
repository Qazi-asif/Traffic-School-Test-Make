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
        Schema::table('course_chapters', function (Blueprint $table) {
            // Drop the foreign key constraint that references florida_courses
            // This allows course_chapters to work with both 'courses' and 'florida_courses' tables
            if (Schema::hasColumn('course_chapters', 'course_id')) {
                // Try to drop the foreign key if it exists
                try {
                    $table->dropForeign(['course_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name, continue
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_chapters', function (Blueprint $table) {
            // Re-add the foreign key constraint to florida_courses
            // Note: This will only work if all course_id values reference florida_courses
            if (Schema::hasTable('florida_courses')) {
                $table->foreign('course_id')
                    ->references('id')
                    ->on('florida_courses')
                    ->onDelete('cascade');
            }
        });
    }
};
