<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nevada_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->string('nevada_dmv_number')->nullable();
            $table->string('court_case_number')->nullable();
            $table->string('court_name')->nullable();
            $table->date('citation_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('offense_code')->nullable();
            $table->timestamps();

            $table->index('nevada_dmv_number');
            $table->index('court_case_number');
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nevada_students');
    }
};
