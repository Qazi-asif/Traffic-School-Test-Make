<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('report_type', ['school_activity', 'certificate_usage', 'submission_errors', 'revenue']);
            $table->date('report_date');
            $table->date('data_range_start');
            $table->date('data_range_end');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_compliance_reports');
    }
};
