<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['course_id']);
            
            // Add course_table field to distinguish between course types
            $table->string('course_table')->default('courses')->after('course_id');
            
            // Make course_id nullable and remove the constraint
            $table->unsignedBigInteger('course_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Remove the course_table field
            $table->dropColumn('course_table');
            
            // Re-add the foreign key constraint
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }
};