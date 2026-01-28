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
        // Create user_course_enrollments table if it doesn't exist
        if (!Schema::hasTable('user_course_enrollments')) {
            Schema::create('user_course_enrollments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('course_id');
                $table->string('course_table')->default('florida_courses');
                $table->enum('status', ['enrolled', 'in_progress', 'completed', 'failed', 'suspended'])->default('enrolled');
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('final_score', 5, 2)->nullable();
                $table->integer('attempts')->default(0);
                $table->json('quiz_scores')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'course_id', 'course_table']);
                $table->index(['status', 'payment_status']);
            });
        }

        // Create chapters table if it doesn't exist
        if (!Schema::hasTable('chapters')) {
            Schema::create('chapters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->string('course_table')->default('florida_courses');
                $table->string('title');
                $table->longText('content')->nullable();
                $table->integer('duration')->default(0); // in minutes
                $table->integer('required_min_time')->default(0); // minimum time to spend
                $table->integer('order_index')->default(0);
                $table->string('video_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('has_quiz')->default(false);
                $table->integer('quiz_questions_count')->default(0);
                $table->timestamps();
                
                $table->index(['course_id', 'course_table', 'order_index']);
                $table->index(['is_active']);
            });
        }

        // Create questions table if it doesn't exist
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chapter_id')->nullable();
                $table->unsignedBigInteger('course_id');
                $table->text('question_text');
                $table->enum('question_type', ['multiple_choice', 'true_false', 'fill_blank', 'essay'])->default('multiple_choice');
                $table->json('options')->nullable(); // For multiple choice options
                $table->text('correct_answer');
                $table->text('explanation')->nullable();
                $table->integer('points')->default(1);
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['chapter_id', 'course_id', 'is_active']);
            });
        }

        // Create final_exam_questions table if it doesn't exist
        if (!Schema::hasTable('final_exam_questions')) {
            Schema::create('final_exam_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->text('question_text');
                $table->enum('question_type', ['multiple_choice', 'true_false', 'fill_blank'])->default('multiple_choice');
                $table->json('options')->nullable();
                $table->text('correct_answer');
                $table->text('explanation')->nullable();
                $table->integer('points')->default(1);
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['course_id', 'is_active']);
            });
        }

        // Create user_course_progress table if it doesn't exist
        if (!Schema::hasTable('user_course_progress')) {
            Schema::create('user_course_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enrollment_id');
                $table->unsignedBigInteger('chapter_id');
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->integer('time_spent')->default(0); // in seconds
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['enrollment_id', 'chapter_id']);
                $table->index(['enrollment_id', 'progress_percentage']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_progress');
        Schema::dropIfExists('final_exam_questions');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('user_course_enrollments');
    }
};