<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('chapter_progress')) {
            Schema::create('chapter_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('quiz_score', 5, 2)->nullable();
                $table->boolean('quiz_passed')->default(false);
                $table->integer('time_spent_minutes')->default(0);
                $table->enum('status', ['not_started', 'in_progress', 'quiz_failed', 'completed'])->default('not_started');
                $table->timestamps();

                $table->unique(['user_id', 'chapter_id']);
            });
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_attempts', 'quiz_type')) {
                $table->string('quiz_type')->default('chapter')->after('id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapter_progress');
        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_attempts', 'quiz_type')) {
                $table->dropColumn('quiz_type');
            }
        });
    }
};
