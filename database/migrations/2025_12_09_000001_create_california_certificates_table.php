<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('california_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('certificate_number')->nullable();
            $table->string('cc_seq_nbr')->nullable(); // TVCC sequence number
            $table->string('cc_stat_cd')->nullable(); // TVCC status code
            $table->timestamp('cc_sub_tstamp')->nullable(); // TVCC submission timestamp
            $table->string('court_code')->nullable(); // CTSI court code
            $table->string('student_name');
            $table->date('completion_date');
            $table->string('driver_license')->nullable();
            $table->date('birth_date');
            $table->string('citation_number')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('enrollment_id');
            $table->index('status');
            $table->index('completion_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('california_certificates');
    }
};
