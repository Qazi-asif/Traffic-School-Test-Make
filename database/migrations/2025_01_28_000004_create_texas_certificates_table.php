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
        Schema::create('texas_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->string('certificate_number')->unique();
            $table->string('student_name');
            $table->string('course_name');
            $table->date('completion_date');
            $table->decimal('final_exam_score', 5, 2);
            $table->integer('defensive_driving_hours')->default(6);
            $table->string('tdlr_course_id')->nullable();
            $table->string('approval_number')->nullable();
            $table->text('student_address')->nullable();
            $table->date('student_date_of_birth')->nullable();
            $table->string('driver_license_number')->nullable();
            $table->string('citation_number')->nullable();
            $table->string('court_name')->nullable();
            $table->string('citation_county')->nullable();
            $table->date('traffic_school_due_date')->nullable();
            $table->string('verification_hash', 32)->unique();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_sent_to_student')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_sent_to_state')->default(false);
            $table->timestamp('state_submission_date')->nullable();
            $table->timestamp('generated_at');
            $table->string('status')->default('generated');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enrollment_id')->references('id')->on('user_course_enrollments')->onDelete('cascade');
            
            $table->index(['status', 'created_at']);
            $table->index(['is_sent_to_student', 'created_at']);
            $table->index(['is_sent_to_state', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('texas_certificates');
    }
};