<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('student_name');
            $table->string('course_name');
            $table->string('state_code', 2);
            $table->date('completion_date');
            $table->timestamp('issued_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('verification_hash')->unique();
            $table->boolean('is_sent_to_state')->default(false);
            $table->string('state_submission_id')->nullable();
            $table->integer('submission_attempts')->default(0);
            $table->timestamp('last_submission_attempt')->nullable();
            $table->enum('status', ['generated', 'submitted', 'confirmed', 'failed'])->default('generated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
