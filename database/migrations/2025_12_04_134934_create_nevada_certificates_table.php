<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nevada_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->nullable()->constrained('certificates')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('nevada_certificate_number')->unique();
            $table->date('completion_date');
            $table->enum('submission_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('submission_date')->nullable();
            $table->text('submission_response')->nullable();
            $table->timestamps();

            $table->index('nevada_certificate_number');
            $table->index('submission_status');
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nevada_certificates');
    }
};
