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
        // Ensure florida_courses table exists with all required fields
        if (!Schema::hasTable('florida_courses')) {
            Schema::create('florida_courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('course_details')->nullable();
                $table->string('state_code', 2)->default('FL');
                $table->integer('min_pass_score')->default(80);
                $table->integer('duration')->default(240); // minutes
                $table->decimal('price', 8, 2)->default(0.00);
                $table->string('dicds_course_id')->nullable();
                $table->string('certificate_template')->nullable();
                $table->boolean('copyright_protected')->default(false);
                $table->integer('passing_score')->default(80);
                $table->boolean('is_active')->default(true);
                $table->string('course_type')->default('BDI');
                $table->string('delivery_type')->default('Internet');
                $table->string('certificate_type')->default('florida_certificate');
                $table->boolean('strict_duration_enabled')->default(true);
                $table->timestamps();
                
                $table->index(['state_code', 'is_active']);
                $table->index('course_type');
            });
        }

        // Ensure missouri_courses table exists with all required fields
        if (!Schema::hasTable('missouri_courses')) {
            Schema::create('missouri_courses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('missouri_course_code')->nullable();
                $table->string('course_type')->default('defensive_driving');
                $table->string('form_4444_template')->nullable();
                $table->boolean('requires_form_4444')->default(true);
                $table->decimal('required_hours', 4, 2)->default(8.00);
                $table->integer('max_completion_days')->default(90);
                $table->string('approval_number')->nullable();
                $table->date('approved_date')->nullable();
                $table->date('expiration_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('state_code', 2)->default('MO');
                $table->integer('passing_score')->default(70);
                $table->decimal('price', 8, 2)->default(0.00);
                $table->integer('duration')->default(480); // 8 hours in minutes
                $table->string('certificate_type')->default('missouri_certificate');
                $table->boolean('strict_duration_enabled')->default(false);
                $table->timestamps();
                
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->index(['is_active', 'course_type']);
            });
        }

        // Ensure texas_courses table exists with all required fields
        if (!Schema::hasTable('texas_courses')) {
            Schema::create('texas_courses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('texas_course_code')->nullable();
                $table->string('tdlr_course_id')->nullable();
                $table->string('course_type')->default('defensive_driving');
                $table->boolean('requires_proctoring')->default(false);
                $table->integer('defensive_driving_hours')->default(6);
                $table->decimal('required_hours', 4, 2)->default(6.00);
                $table->integer('max_completion_days')->default(90);
                $table->string('approval_number')->nullable();
                $table->date('approved_date')->nullable();
                $table->date('expiration_date')->nullable();
                $table->string('certificate_template')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('state_code', 2)->default('TX');
                $table->integer('passing_score')->default(75);
                $table->decimal('price', 8, 2)->default(0.00);
                $table->integer('duration')->default(360); // 6 hours in minutes
                $table->string('certificate_type')->default('texas_certificate');
                $table->boolean('strict_duration_enabled')->default(true);
                $table->timestamps();
                
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->index(['is_active', 'course_type']);
            });
        }

        // Ensure delaware_courses table exists with all required fields
        if (!Schema::hasTable('delaware_courses')) {
            Schema::create('delaware_courses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->string('delaware_course_code')->nullable();
                $table->string('course_type')->default('defensive_driving');
                $table->decimal('required_hours', 4, 2)->default(6.00);
                $table->integer('max_completion_days')->default(90);
                $table->string('approval_number')->nullable();
                $table->date('approved_date')->nullable();
                $table->date('expiration_date')->nullable();
                $table->string('certificate_template')->nullable();
                $table->boolean('quiz_rotation_enabled')->default(true);
                $table->integer('quiz_pool_size')->default(50);
                $table->boolean('is_active')->default(true);
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('state_code', 2)->default('DE');
                $table->integer('passing_score')->default(80);
                $table->decimal('price', 8, 2)->default(0.00);
                $table->integer('duration')->default(360); // 6 hours in minutes (can be 3hr or 6hr)
                $table->string('certificate_type')->default('delaware_certificate');
                $table->boolean('strict_duration_enabled')->default(true);
                $table->string('duration_type')->default('6hr'); // '3hr' or '6hr'
                $table->timestamps();
                
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->index(['is_active', 'course_type']);
                $table->index('duration_type');
            });
        }

        // Update chapters table to support multi-state courses
        if (!Schema::hasColumn('chapters', 'course_table')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->string('course_table')->default('courses')->after('course_id');
                $table->string('state_code', 2)->nullable()->after('course_table');
                
                $table->index(['course_table', 'course_id']);
                $table->index('state_code');
            });
        }

        // Update chapter_questions table to support state-specific questions
        if (Schema::hasTable('chapter_questions') && !Schema::hasColumn('chapter_questions', 'state_specific')) {
            Schema::table('chapter_questions', function (Blueprint $table) {
                $table->string('state_specific', 2)->nullable()->after('is_active');
                $table->integer('difficulty_level')->default(1)->after('state_specific');
                
                $table->index('state_specific');
                $table->index('difficulty_level');
            });
        }

        // Update user_course_enrollments to ensure course_table field exists
        if (!Schema::hasColumn('user_course_enrollments', 'course_table')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                $table->string('course_table')->default('florida_courses')->after('course_id');
                
                $table->index(['course_table', 'course_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns
        if (Schema::hasColumn('user_course_enrollments', 'course_table')) {
            Schema::table('user_course_enrollments', function (Blueprint $table) {
                $table->dropIndex(['course_table', 'course_id']);
                $table->dropColumn('course_table');
            });
        }

        if (Schema::hasTable('chapter_questions')) {
            Schema::table('chapter_questions', function (Blueprint $table) {
                if (Schema::hasColumn('chapter_questions', 'state_specific')) {
                    $table->dropIndex(['state_specific']);
                    $table->dropIndex(['difficulty_level']);
                    $table->dropColumn(['state_specific', 'difficulty_level']);
                }
            });
        }

        if (Schema::hasColumn('chapters', 'course_table')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropIndex(['course_table', 'course_id']);
                $table->dropIndex(['state_code']);
                $table->dropColumn(['course_table', 'state_code']);
            });
        }

        // Note: We don't drop the state-specific course tables as they may contain important data
        // If you need to drop them, uncomment the lines below:
        // Schema::dropIfExists('delaware_courses');
        // Schema::dropIfExists('texas_courses');
        // Schema::dropIfExists('missouri_courses');
        // Schema::dropIfExists('florida_courses');
    }
};