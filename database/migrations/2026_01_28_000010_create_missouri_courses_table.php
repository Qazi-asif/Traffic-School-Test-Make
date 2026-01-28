<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missouri_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('missouri_course_code');
            $table->enum('course_type', ['defensive_driving', 'point_reduction', 'dui_education']);
            $table->string('form_4444_template')->nullable();
            $table->boolean('requires_form_4444')->default(true);
            $table->decimal('required_hours', 4, 2)->default(8.00);
            $table->integer('max_completion_days')->default(90);
            $table->string('approval_number')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('missouri_course_code');
            $table->index('course_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missouri_courses');
    }
};