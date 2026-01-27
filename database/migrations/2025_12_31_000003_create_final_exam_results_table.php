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
        Schema::create('final_exam_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('course_id');
            $table->string('course_type')->default('courses'); // 'courses' or 'florida_courses'
            
            // Exam Details
            $table->decimal('final_exam_score', 5, 2)->default(0); // Final exam percentage
            $table->integer('final_exam_correct')->default(0);
            $table->integer('final_exam_total')->default(0);
            $table->timestamp('exam_completed_at');
            $table->integer('exam_duration_minutes')->default(0);
            
            // Overall Scores
            $table->decimal('quiz_average', 5, 2)->default(0); // Chapter quizzes average
            $table->decimal('free_response_score', 5, 2)->nullable(); // Free response total score
            $table->decimal('overall_score', 5, 2)->default(0); // Calculated overall score
            $table->string('grade_letter', 2)->nullable(); // A, B, C, D, F
            
            // Status and Grading
            $table->enum('status', ['pending', 'passed', 'failed', 'under_review'])->default('pending');
            $table->boolean('is_passing')->default(false);
            $table->decimal('passing_threshold', 5, 2)->default(80.00);
            
            // Student Feedback
            $table->text('student_feedback')->nullable();
            $table->integer('student_rating')->nullable(); // 1-5 stars
            $table->timestamp('student_feedback_at')->nullable();
            
            // Instructor Grading Period
            $table->timestamp('grading_period_ends_at'); // 24 hours after exam completion
            $table->boolean('grading_completed')->default(false);
            $table->text('instructor_notes')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->timestamp('graded_at')->nullable();
            
            // Certificate
            $table->boolean('certificate_generated')->default(false);
            $table->string('certificate_number')->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'enrollment_id']);
            $table->index(['course_id', 'course_type']);
            $table->index(['status']);
            $table->index(['grading_period_ends_at']);
            $table->index(['is_passing']);
        });

        // Table for detailed question results
        Schema::create('final_exam_question_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('final_exam_result_id');
            $table->unsignedBigInteger('question_id');
            $table->string('student_answer')->nullable();
            $table->string('correct_answer');
            $table->boolean('is_correct')->default(false);
            $table->decimal('points_earned', 5, 2)->default(0);
            $table->decimal('points_possible', 5, 2)->default(1);
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->foreign('final_exam_result_id')->references('id')->on('final_exam_results')->onDelete('cascade');
            $table->index(['final_exam_result_id']);
            $table->index(['question_id']);
        });

        // Add columns to existing tables if they don't exist
        if (Schema::hasTable('user_course_enrollments')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('user_course_enrollments', 'final_exam_completed')) {
                    $table->boolean('final_exam_completed')->default(false);
                }
                if (!Schema::hasColumn('user_course_enrollments', 'final_exam_result_id')) {
                    $table->unsignedBigInteger('final_exam_result_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_exam_question_results');
        Schema::dropIfExists('final_exam_results');
        
        if (Schema::hasTable('user_course_enrollments')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                $table->dropColumn(['final_exam_completed', 'final_exam_result_id']);
            });
        }
    }
};