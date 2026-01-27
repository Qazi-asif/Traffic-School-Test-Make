<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Missouri Form 4444 Management
        Schema::create('missouri_form4444s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('form_number')->unique();
            $table->timestamp('completion_date')->nullable();
            $table->timestamp('submission_deadline')->nullable();
            $table->enum('submission_method', ['point_reduction', 'court_ordered', 'insurance_discount', 'voluntary']);
            $table->boolean('court_signature_required')->default(false);
            $table->boolean('submitted_to_dor')->default(false);
            $table->timestamp('dor_submission_date')->nullable();
            $table->enum('status', ['pending_completion', 'ready_for_submission', 'awaiting_court_signature', 'submitted_to_dor', 'expired'])->default('pending_completion');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // Missouri Course Structure
        Schema::create('missouri_course_structures', function (Blueprint $table) {
            $table->id();
            $table->integer('chapter_number');
            $table->string('chapter_title');
            $table->longText('content')->nullable();
            $table->integer('quiz_questions_count')->default(10);
            $table->integer('passing_score')->default(80);
            $table->integer('time_requirement_minutes')->default(30);
            $table->timestamps();
        });

        // Missouri Quiz Bank
        Schema::create('missouri_quiz_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->nullable()->constrained('missouri_course_structures')->onDelete('cascade');
            $table->text('question_text');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->string('option_e')->nullable();
            $table->enum('correct_answer', ['A', 'B', 'C', 'D', 'E']);
            $table->enum('category', ['traffic_laws', 'road_signs', 'safe_driving', 'alcohol_drugs', 'defensive_driving'])->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('state_required')->default(true);
            $table->boolean('is_final_exam')->default(false);
            $table->timestamps();
        });

        // Missouri Submission Tracker
        Schema::create('missouri_submission_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_4444_id')->constrained('missouri_form4444s')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('completion_date');
            $table->timestamp('submission_deadline');
            $table->integer('days_remaining')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->enum('status', ['active', 'submitted', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Missouri Students
        Schema::create('missouri_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('missouri_license_number')->nullable();
            $table->string('court_case_number')->nullable();
            $table->foreignId('county_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('reason_attending', ['point_reduction', 'court_ordered', 'insurance_discount', 'voluntary']);
            $table->date('completion_deadline')->nullable();
            $table->date('certificate_mailed_date')->nullable();
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'expired'])->default('enrolled');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('missouri_submission_trackers');
        Schema::dropIfExists('missouri_students');
        Schema::dropIfExists('missouri_quiz_banks');
        Schema::dropIfExists('missouri_course_structures');
        Schema::dropIfExists('missouri_form4444s');
    }
};
