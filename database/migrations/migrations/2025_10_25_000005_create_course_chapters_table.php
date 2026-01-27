<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('order_index')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('required_min_time')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });

        // Add foreign key only if parent table exists
        if (Schema::hasTable('florida_courses')) {
            Schema::table('course_chapters', function (Blueprint $table) {
                $table->foreign('course_id')
                    ->references('id')
                    ->on('florida_courses')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('course_chapters', function (Blueprint $table) {
            if (Schema::hasColumn('course_chapters', 'course_id')) {
                $table->dropForeign(['course_id']);
            }
        });

        Schema::dropIfExists('course_chapters');
    }
};
