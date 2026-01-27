<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
            $table->foreignId('chapter_id')->nullable()->change();
            $table->foreign('chapter_id')->references('id')->on('missouri_course_structures')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
            $table->foreignId('chapter_id')->nullable(false)->change();
            $table->foreign('chapter_id')->references('id')->on('missouri_course_structures')->onDelete('cascade');
        });
    }
};
