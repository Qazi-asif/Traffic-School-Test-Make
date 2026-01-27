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
        Schema::create('quiz_random_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('course_table')->default('courses'); // 'courses' or 'florida_courses'
            $table->unsignedBigInteger('chapter_id')->nullable(); // null = final exam
            $table->boolean('use_random_selection')->default(false);
            $table->integer('questions_to_select')->default(10);
            $table->timestamps();

            // Foreign keys - Note: We can't add foreign key constraints since course_id might reference different tables
            // $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');

            // Unique constraint - one setting per course/chapter/table combination
            $table->unique(['course_id', 'chapter_id', 'course_table']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_random_settings');
    }
};