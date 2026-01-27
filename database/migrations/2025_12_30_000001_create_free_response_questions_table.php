<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_response_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->text('question_text');
            $table->text('sample_answer')->nullable();
            $table->text('grading_rubric')->nullable();
            $table->integer('points')->default(5);
            $table->integer('order_index');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['course_id', 'is_active']);
            $table->index('order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_response_questions');
    }
};