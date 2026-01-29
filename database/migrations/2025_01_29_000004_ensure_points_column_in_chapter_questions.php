<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapter_questions', function (Blueprint $table) {
            // Add points column if it doesn't exist
            if (!Schema::hasColumn('chapter_questions', 'points')) {
                $table->integer('points')->default(1)->after('correct_answer');
            }
            
            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('chapter_questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('order_index');
            }
            
            // Ensure question_type column exists
            if (!Schema::hasColumn('chapter_questions', 'question_type')) {
                $table->enum('question_type', ['multiple_choice', 'true_false'])->default('multiple_choice')->after('question_text');
            }
            
            // Ensure options column exists
            if (!Schema::hasColumn('chapter_questions', 'options')) {
                $table->json('options')->nullable()->after('question_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chapter_questions', function (Blueprint $table) {
            $table->dropColumn(['points', 'is_active']);
        });
    }
};