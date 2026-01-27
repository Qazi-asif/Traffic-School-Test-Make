<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
            $table->foreign('chapter_id')->references('id')->on('course_chapters')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }
};
