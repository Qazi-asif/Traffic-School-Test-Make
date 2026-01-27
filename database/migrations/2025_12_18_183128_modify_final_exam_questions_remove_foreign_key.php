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
        Schema::table('final_exam_questions', function (Blueprint $table) {
            // Drop the foreign key constraint to allow course_id from both tables
            $table->dropForeign(['course_id']);
            
            // Keep the course_id column as unsigned big integer (no constraint)
            // This allows referencing both 'courses' and 'florida_courses' tables
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_exam_questions', function (Blueprint $table) {
            // Re-add the foreign key constraint (rollback)
            $table->foreign('course_id')->references('id')->on('florida_courses')->onDelete('cascade');
        });
    }
};
