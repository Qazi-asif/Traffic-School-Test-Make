<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_response_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enrollment_id')->nullable();
            $table->text('answer_text');
            $table->integer('word_count');
            $table->decimal('score', 5, 2)->nullable(); // Admin can grade out of question points
            $table->text('feedback')->nullable(); // Admin feedback
            $table->enum('status', ['submitted', 'graded', 'needs_review'])->default('submitted');
            $table->timestamp('submitted_at');
            $table->timestamp('graded_at')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable(); // Admin who graded
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('question_id')->references('id')->on('free_response_questions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('graded_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['question_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index('enrollment_id');
            $table->index('status');
            
            // Unique constraint to prevent duplicate answers
            $table->unique(['question_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_response_answers');
    }
};