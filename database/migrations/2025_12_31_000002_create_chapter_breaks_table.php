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
        Schema::create('chapter_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('course_type')->default('courses'); // 'courses' or 'florida_courses'
            $table->integer('after_chapter_id'); // Chapter after which break should occur
            $table->string('break_title')->default('Study Break');
            $table->text('break_message')->nullable();
            $table->integer('break_duration_hours')->default(1); // Break duration in hours
            $table->integer('break_duration_minutes')->default(0); // Additional minutes
            $table->boolean('is_mandatory')->default(true); // Whether break can be skipped
            $table->boolean('is_active')->default(true);
            $table->json('break_settings')->nullable(); // Additional settings like background color, etc.
            $table->timestamps();

            $table->index(['course_id', 'course_type']);
            $table->index(['after_chapter_id']);
            $table->index(['is_active']);
        });

        // Table to track student break sessions
        Schema::create('student_break_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('chapter_break_id');
            $table->timestamp('break_started_at');
            $table->timestamp('break_ends_at');
            $table->timestamp('break_completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('was_skipped')->default(false);
            $table->json('break_data')->nullable(); // Store any additional break session data
            $table->timestamps();

            $table->index(['user_id', 'enrollment_id']);
            $table->index(['chapter_break_id']);
            $table->index(['break_ends_at']);
            $table->index(['is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_break_sessions');
        Schema::dropIfExists('chapter_breaks');
    }
};