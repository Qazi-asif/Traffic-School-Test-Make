<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('free_response_quiz_placements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('after_chapter_id')->nullable(); // null means at the end
            $table->string('quiz_title')->default('Free Response Questions');
            $table->text('quiz_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('order_index')->default(1);
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
            $table->index(['after_chapter_id']);
        });

        // Add placement_id to free_response_questions table
        Schema::table('free_response_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('free_response_questions', 'placement_id')) {
                $table->unsignedBigInteger('placement_id')->nullable()->after('course_id');
                $table->index(['placement_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('free_response_questions', function (Blueprint $table) {
            $table->dropColumn('placement_id');
        });
        
        Schema::dropIfExists('free_response_quiz_placements');
    }
};