<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('missouri_quiz_banks', 'chapter')) {
                $table->integer('chapter')->after('id');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'question')) {
                $table->text('question')->after('chapter');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'difficulty')) {
                $table->string('difficulty')->default('medium')->after('correct_answer');
            }
            if (! Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
                $table->boolean('is_final_exam')->default(false)->after('difficulty');
            }
        });
    }

    public function down()
    {
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->dropColumn(['chapter', 'question', 'option_e', 'difficulty', 'is_final_exam']);
        });
    }
};
