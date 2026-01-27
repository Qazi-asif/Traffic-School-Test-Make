<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add quiz_set column to chapter_questions table if it doesn't exist
        if (!Schema::hasColumn('chapter_questions', 'quiz_set')) {
            Schema::table('chapter_questions', function (Blueprint $table) {
                $table->integer('quiz_set')->default(1)->after('correct_answer')->comment('1 or 2 for rotating quizzes (Delaware only)');
            });
        }

        // Add quiz tracking columns to user_course_progress table
        Schema::table('user_course_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('user_course_progress', 'quiz_score')) {
                $table->integer('quiz_score')->nullable()->after('is_completed');
            }
            if (!Schema::hasColumn('user_course_progress', 'current_quiz_set')) {
                $table->integer('current_quiz_set')->default(1)->after('quiz_score')->comment('Current quiz set being attempted');
            }
            if (!Schema::hasColumn('user_course_progress', 'quiz_set_1_attempts')) {
                $table->integer('quiz_set_1_attempts')->default(0)->after('current_quiz_set')->comment('Number of attempts on quiz set 1');
            }
            if (!Schema::hasColumn('user_course_progress', 'quiz_set_2_attempts')) {
                $table->integer('quiz_set_2_attempts')->default(0)->after('quiz_set_1_attempts')->comment('Number of attempts on quiz set 2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chapter_questions', function (Blueprint $table) {
            if (Schema::hasColumn('chapter_questions', 'quiz_set')) {
                $table->dropColumn('quiz_set');
            }
        });

        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->dropColumn(['current_quiz_set', 'quiz_set_1_attempts', 'quiz_set_2_attempts']);
        });
    }
};