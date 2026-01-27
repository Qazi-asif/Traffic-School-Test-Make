<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('dicds_certificate_number');
            $table->string('student_name');
            $table->date('completion_date');
            $table->string('course_name');
            $table->decimal('final_exam_score', 5, 2);
            $table->string('driver_license_number');
            $table->string('citation_number');
            $table->string('citation_county');
            $table->date('traffic_school_due_date');
            $table->text('student_address');
            $table->date('student_date_of_birth');
            $table->string('court_name');
            $table->string('state')->default('Florida');
            $table->string('pdf_path')->nullable();
            $table->string('verification_hash')->unique();
            $table->boolean('is_sent_to_student')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_certificates');
    }
};
