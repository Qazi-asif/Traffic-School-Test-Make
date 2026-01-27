<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('chapter_id')->nullable()->constrained()->onDelete('cascade');
                $table->text('question_text');
                $table->string('question_type')->default('multiple_choice');
                $table->json('options')->nullable();
                $table->text('correct_answer');
                $table->text('explanation')->nullable();
                $table->integer('points')->default(1);
                $table->integer('order_index')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
