<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delaware_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('delaware_course_code');
            $table->enum('course_type', ['defensive_driving', 'point_reduction', 'dui_education']);
            $table->decimal('required_hours', 4, 2)->default(8.00);
            $table->integer('max_completion_days')->default(90);
            $table->string('approval_number')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('certificate_template')->nullable();
            $table->boolean('quiz_rotation_enabled')->default(true);
            $table->integer('quiz_pool_size')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('delaware_course_code');
            $table->index('course_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delaware_courses');
    }
};