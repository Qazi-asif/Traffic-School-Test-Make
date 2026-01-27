<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE missouri_quiz_banks ADD COLUMN IF NOT EXISTS option_e VARCHAR(255)');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D', 'E'))");
    }

    public function down()
    {
        DB::statement('ALTER TABLE missouri_quiz_banks DROP COLUMN IF EXISTS option_e');
        DB::statement('ALTER TABLE missouri_quiz_banks DROP CONSTRAINT IF EXISTS missouri_quiz_banks_correct_answer_check');
        DB::statement("ALTER TABLE missouri_quiz_banks ADD CONSTRAINT missouri_quiz_banks_correct_answer_check CHECK (correct_answer IN ('A', 'B', 'C', 'D'))");
    }
};
