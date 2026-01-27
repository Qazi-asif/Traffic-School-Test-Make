<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('florida_certificates', function (Blueprint $table) {
            $table->decimal('final_exam_score', 5, 2)->nullable()->change();
            $table->string('driver_license_number')->nullable()->change();
            $table->string('citation_number')->nullable()->change();
            $table->string('citation_county')->nullable()->change();
            $table->date('traffic_school_due_date')->nullable()->change();
            $table->text('student_address')->nullable()->change();
            $table->date('student_date_of_birth')->nullable()->change();
            $table->string('court_name')->nullable()->change();
            $table->timestamp('generated_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('florida_certificates', function (Blueprint $table) {
            $table->decimal('final_exam_score', 5, 2)->nullable(false)->change();
            $table->string('driver_license_number')->nullable(false)->change();
            $table->string('citation_number')->nullable(false)->change();
            $table->string('citation_county')->nullable(false)->change();
            $table->date('traffic_school_due_date')->nullable(false)->change();
            $table->text('student_address')->nullable(false)->change();
            $table->date('student_date_of_birth')->nullable(false)->change();
            $table->string('court_name')->nullable(false)->change();
            $table->timestamp('generated_at')->nullable(false)->change();
        });
    }
};
