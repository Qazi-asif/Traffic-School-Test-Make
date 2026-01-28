<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('texas_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('texas_course_code');
            $table->string('tdlr_course_id')->nullable();
            $table->enum('course_type', ['defensive_driving', 'driving_safety', 'dui_education']);
            $table->boolean('requires_proctoring')->default(false);
            $table->integer('defensive_driving_hours')->default(6);
            $table->decimal('required_hours', 4, 2)->default(6.00);
            $table->integer('max_completion_days')->default(90);
            $table->string('approval_number')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('certificate_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('texas_course_code');
            $table->index('tdlr_course_id');
            $table->index('course_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('texas_courses');
    }
};