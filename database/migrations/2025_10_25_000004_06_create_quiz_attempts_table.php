<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();

                // Foreign keys (keeping all possible relations)
                $table->foreignId('enrollment_id')->nullable()->constrained('user_course_enrollments')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
                $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('cascade');

                // Quiz data
                $table->json('questions_attempted')->nullable();
                $table->json('answers')->nullable();

                // Scoring
                $table->decimal('score', 5, 2)->nullable()->default(0);
                $table->decimal('percentage', 5, 2)->nullable()->default(0);
                $table->integer('total_questions')->nullable()->default(0);
                $table->boolean('passed')->nullable()->default(false);
                $table->integer('time_spent')->nullable();

                // Timing
                $table->timestamp('attempted_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
