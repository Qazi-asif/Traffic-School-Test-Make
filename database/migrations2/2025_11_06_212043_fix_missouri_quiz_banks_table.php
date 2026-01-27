<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the 'chapter' column if it exists (from old migration)
        if (Schema::hasColumn('missouri_quiz_banks', 'chapter')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->dropColumn('chapter');
            });
        }

        // Make chapter_id nullable
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_chapter_id_foreign');
        DB::statement('ALTER TABLE missouri_quiz_banks ALTER COLUMN chapter_id DROP NOT NULL');

        // Add option_e if not exists
        if (! Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->string('option_e')->nullable()->after('option_d');
            });
        }

        // Update correct_answer constraint to include E
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D', 'E'))");

        // Add is_final_exam if not exists
        if (! Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->boolean('is_final_exam')->default(false)->after('state_required');
            });
        }

        // Drop category column if exists
        if (Schema::hasColumn('missouri_quiz_banks', 'category')) {
            Schema::table('missouri_quiz_banks', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        // Re-add foreign key constraint
        DB::statement('ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_chapter_id_foreign FOREIGN KEY (chapter_id) REFERENCES missouri_course_structures(id) ON DELETE CASCADE');
    }

    public function down()
    {
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_chapter_id_foreign');
        DB::statement('ALTER TABLE missouri_quiz_banks ALTER COLUMN chapter_id SET NOT NULL');

        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->dropColumn(['option_e', 'is_final_exam']);
            $table->integer('chapter')->nullable();
            $table->enum('category', ['traffic_laws', 'road_signs', 'safe_driving', 'alcohol_drugs', 'defensive_driving'])->after('correct_answer');
        });

        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D'))");

        DB::statement('ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_chapter_id_foreign FOREIGN KEY (chapter_id) REFERENCES missouri_course_structures(id) ON DELETE CASCADE');
    }
};
