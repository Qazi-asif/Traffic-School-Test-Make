<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_activity_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->foreignId('school_id')->constrained('florida_schools')->onDelete('cascade');
            $table->enum('course_type', ['BDI', 'ADI', 'TLSAE']);
            $table->integer('certificates_issued');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->json('report_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_activity_reports');
    }
};
