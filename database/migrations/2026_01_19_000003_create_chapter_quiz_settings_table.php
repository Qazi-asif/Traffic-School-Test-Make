<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_quiz_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('chapter_id');
            $table->string('course_table')->default('courses'); // 'courses' or 'florida_courses'
            $table->integer('questions_to_select')->default(10);
            $table->integer('total_questions_in_pool')->default(0); // Auto-calculated
            $table->boolean('use_random_selection')->default(false);
            $table->timestamps();

            $table->unique(['course_id', 'chapter_id', 'course_table']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_quiz_settings');
    }
};