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
        Schema::table('timer_sessions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['course_timer_id']);
            
            // Add new foreign key constraint pointing to chapter_timers
            $table->foreign('course_timer_id')->references('id')->on('chapter_timers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timer_sessions', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['course_timer_id']);
            
            // Restore the original foreign key constraint
            $table->foreign('course_timer_id')->references('id')->on('course_timers')->onDelete('cascade');
        });
    }
};