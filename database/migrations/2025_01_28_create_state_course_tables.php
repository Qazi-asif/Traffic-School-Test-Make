<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create Missouri courses table
        Schema::create('missouri_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('missouri_course_code');
            $table->enum('course_type', ['defensive_driving', 'point_reduction', 'dui_education'])->default('defensive_driving');
            $table->string('form_4444_template')->nullable();
            $table->boolean('requires_form_4444')->default(true);
            $table->decimal('required_hours', 4, 2)->default(8.00);
            $table->integer('max_completion_days')->default(90);
            $table->string('approval_number')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['missouri_course_code', 'course_type', 'is_active']);
        });

        // Create Texas courses table
        Schema::create('texas_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('texas_course_code');
            $table->string('tdlr_course_id')->nullable();
            $table->enum('course_type', ['defensive_driving', 'driving_safety', 'dui_education'])->default('defensive_driving');
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
            
            $table->index(['texas_course_code', 'tdlr_course_id', 'course_type', 'is_active']);
        });

        // Create Delaware courses table
        Schema::create('delaware_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('delaware_course_code');
            $table->enum('course_type', ['defensive_driving', 'point_reduction', 'dui_education'])->default('defensive_driving');
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
            
            $table->index(['delaware_course_code', 'course_type', 'is_active']);
        });

        // Create Nevada courses table
        Schema::create('nevada_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('nevada_course_code');
            $table->enum('course_type', ['defensive_driving', 'traffic_safety', 'dui_education'])->default('defensive_driving');
            $table->decimal('required_hours', 4, 2)->default(4.00);
            $table->integer('max_completion_days')->default(90);
            $table->string('approval_number')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('certificate_template')->nullable();
            $table->boolean('ntsa_enabled')->default(false);
            $table->string('ntsa_court_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['nevada_course_code', 'course_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nevada_courses');
        Schema::dropIfExists('delaware_courses');
        Schema::dropIfExists('texas_courses');
        Schema::dropIfExists('missouri_courses');
    }
};