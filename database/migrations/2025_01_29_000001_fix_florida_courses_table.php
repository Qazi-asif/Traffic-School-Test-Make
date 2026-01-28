<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ensure florida_courses table exists with all required columns
        if (!Schema::hasTable('florida_courses')) {
            Schema::create('florida_courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('state', 50)->default('FL');
                $table->string('state_code', 10)->nullable();
                $table->integer('duration')->default(240);
                $table->integer('total_duration')->nullable();
                $table->decimal('price', 8, 2)->default(0);
                $table->integer('passing_score')->default(80);
                $table->integer('min_pass_score')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('course_type')->default('BDI');
                $table->string('delivery_type')->default('Online');
                $table->string('certificate_type')->nullable();
                $table->string('certificate_template')->nullable();
                $table->string('dicds_course_id')->nullable();
                $table->timestamps();
            });
        } else {
            // Add missing columns to existing table
            Schema::table('florida_courses', function (Blueprint $table) {
                if (!Schema::hasColumn('florida_courses', 'state_code')) {
                    $table->string('state_code', 10)->nullable();
                }
                if (!Schema::hasColumn('florida_courses', 'total_duration')) {
                    $table->integer('total_duration')->nullable();
                }
                if (!Schema::hasColumn('florida_courses', 'min_pass_score')) {
                    $table->integer('min_pass_score')->nullable();
                }
                if (!Schema::hasColumn('florida_courses', 'certificate_template')) {
                    $table->string('certificate_template')->nullable();
                }
                if (!Schema::hasColumn('florida_courses', 'delivery_type')) {
                    $table->string('delivery_type')->nullable();
                }
                if (!Schema::hasColumn('florida_courses', 'dicds_course_id')) {
                    $table->string('dicds_course_id')->nullable();
                }
            });
        }

        // Ensure users table has role column
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user');
            });
        }

        // Create push_notifications table if it doesn't exist
        if (!Schema::hasTable('push_notifications')) {
            Schema::create('push_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('info');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Don't drop tables in down method for safety
    }
};