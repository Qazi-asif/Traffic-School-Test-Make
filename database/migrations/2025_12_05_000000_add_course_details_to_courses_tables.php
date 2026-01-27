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
        // Add course_details to courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->text('course_details')->nullable()->after('description');
        });

        // Add course_details to florida_courses table
        Schema::table('florida_courses', function (Blueprint $table) {
            $table->text('course_details')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('course_details');
        });

        Schema::table('florida_courses', function (Blueprint $table) {
            $table->dropColumn('course_details');
        });
    }
};
