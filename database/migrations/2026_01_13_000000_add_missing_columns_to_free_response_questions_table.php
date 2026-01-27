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
        Schema::table('free_response_questions', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('free_response_questions', 'sample_answer')) {
                $table->text('sample_answer')->nullable()->after('question_text');
            }
            
            if (!Schema::hasColumn('free_response_questions', 'grading_rubric')) {
                $table->text('grading_rubric')->nullable()->after('sample_answer');
            }
            
            if (!Schema::hasColumn('free_response_questions', 'points')) {
                $table->integer('points')->default(5)->after('grading_rubric');
            }
            
            if (!Schema::hasColumn('free_response_questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('free_response_questions', function (Blueprint $table) {
            $table->dropColumn(['sample_answer', 'grading_rubric', 'points', 'is_active']);
        });
    }
};