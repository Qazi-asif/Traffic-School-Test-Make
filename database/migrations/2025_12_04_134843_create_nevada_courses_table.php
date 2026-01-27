<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nevada_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('nevada_course_code');
            $table->enum('course_type', ['traffic_safety', 'defensive_driving', 'dui']);
            $table->date('approved_date');
            $table->date('expiration_date')->nullable();
            $table->string('approval_number')->nullable();
            $table->decimal('required_hours', 4, 2);
            $table->integer('max_completion_days')->default(90);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nevada_course_code');
            $table->index('course_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nevada_courses');
    }
};
