<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS chapter');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS question');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS difficulty');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_chapter_id_foreign');
        DB::statement('ALTER TABLE missouri_quiz_banks ALTER COLUMN chapter_id DROP NOT NULL');
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS option_e VARCHAR(255)');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D', 'E'))");
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS is_final_exam BOOLEAN DEFAULT FALSE');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS category');
        DB::statement('ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_chapter_id_foreign FOREIGN KEY (chapter_id) REFERENCES missouri_course_structures(id) ON DELETE CASCADE');
    }

    public function down()
    {
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_chapter_id_foreign');
        DB::statement('ALTER TABLE missouri_quiz_banks ALTER COLUMN chapter_id SET NOT NULL');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS option_e');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS is_final_exam');
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS chapter INTEGER');
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS question TEXT');
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS difficulty VARCHAR(255)');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D'))");
        DB::statement('ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_chapter_id_foreign FOREIGN KEY (chapter_id) REFERENCES missouri_course_structures(id) ON DELETE CASCADE');
    }
};
