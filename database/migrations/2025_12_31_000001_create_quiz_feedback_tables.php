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
        // Create table for storing multiple choice quiz feedback
        Schema::create('quiz_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('quiz_attempt_id')->nullable(); // Links to specific quiz attempt
            $table->integer('chapter_id')->nullable(); // Which chapter quiz
            $table->string('quiz_type')->default('chapter'); // 'chapter', 'practice', 'review'
            $table->decimal('score', 5, 2)->nullable(); // Quiz score percentage
            $table->integer('correct_answers')->nullable();
            $table->integer('total_questions')->nullable();
            $table->text('instructor_feedback')->nullable(); // Instructor's feedback on the quiz
            $table->enum('status', ['pending', 'reviewed', 'needs_improvement', 'approved'])->default('pending');
            $table->timestamp('feedback_given_at')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable(); // Who gave the feedback
            $table->timestamps();

            $table->index(['enrollment_id', 'chapter_id']);
            $table->index(['status']);
            $table->index(['quiz_type']);
        });

        // Create table for storing individual question feedback within a quiz
        Schema::create('quiz_question_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_feedback_id');
            $table->unsignedBigInteger('question_id');
            $table->string('student_answer')->nullable(); // Student's selected answer
            $table->string('correct_answer')->nullable(); // Correct answer
            $table->boolean('is_correct')->default(false);
            $table->text('question_feedback')->nullable(); // Specific feedback for this question
            $table->text('explanation')->nullable(); // Explanation of correct answer
            $table->timestamps();

            $table->foreign('quiz_feedback_id')->references('id')->on('quiz_feedback')->onDelete('cascade');
            $table->index(['quiz_feedback_id']);
            $table->index(['question_id']);
        });

        // Enhance existing student_feedback table if it doesn't exist
        if (!Schema::hasTable('student_feedback')) {
            Schema::create('student_feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enrollment_id');
                $table->text('instructor_feedback');
                $table->enum('status', ['pending', 'approved', 'needs_improvement'])->default('pending');
                $table->timestamp('feedback_given_at')->nullable();
                $table->unsignedBigInteger('instructor_id')->nullable();
                $table->boolean('can_take_final_exam')->default(false);
                $table->timestamps();

                $table->index(['enrollment_id']);
                $table->index(['status']);
            });
        }

        // Add quiz feedback tracking to user_course_enrollments if columns don't exist
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('user_course_enrollments', 'quiz_feedback_required')) {
                $table->boolean('quiz_feedback_required')->default(false);
            }
            if (!Schema::hasColumn('user_course_enrollments', 'quiz_feedback_completed')) {
                $table->boolean('quiz_feedback_completed')->default(false);
            }
            if (!Schema::hasColumn('user_course_enrollments', 'can_take_final_exam')) {
                $table->boolean('can_take_final_exam')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_question_feedback');
        Schema::dropIfExists('quiz_feedback');
        
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['quiz_feedback_required', 'quiz_feedback_completed', 'can_take_final_exam']);
        });
    }
};