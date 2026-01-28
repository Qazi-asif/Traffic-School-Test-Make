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
        // Create chapter_questions table if it doesn't exist
        if (!Schema::hasTable('chapter_questions')) {
            Schema::create('chapter_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chapter_id');
                $table->text('question_text');
                $table->string('question_type')->default('multiple_choice');
                $table->json('options')->nullable();
                $table->string('correct_answer');
                $table->text('explanation')->nullable();
                $table->integer('points')->default(1);
                $table->integer('order_index')->default(1);
                $table->integer('quiz_set')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['chapter_id']);
                $table->index(['order_index']);
                $table->index(['quiz_set']);
                $table->index(['is_active']);
            });
        } else {
            // Add missing columns if table exists but columns are missing
            Schema::table('chapter_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('chapter_questions', 'quiz_set')) {
                    $table->integer('quiz_set')->default(1)->after('order_index');
                }
                if (!Schema::hasColumn('chapter_questions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('quiz_set');
                }
                if (!Schema::hasColumn('chapter_questions', 'points')) {
                    $table->integer('points')->default(1)->after('explanation');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table as it might contain important data
        // Schema::dropIfExists('chapter_questions');
    }
};