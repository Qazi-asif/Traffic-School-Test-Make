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
        Schema::table('chapter_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('chapter_questions', 'question_type')) {
                $table->string('question_type')->default('multiple_choice')->after('question_text');
            }
            
            if (!Schema::hasColumn('chapter_questions', 'options')) {
                $table->json('options')->nullable()->after('question_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapter_questions', function (Blueprint $table) {
            if (Schema::hasColumn('chapter_questions', 'question_type')) {
                $table->dropColumn('question_type');
            }
            
            if (Schema::hasColumn('chapter_questions', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};