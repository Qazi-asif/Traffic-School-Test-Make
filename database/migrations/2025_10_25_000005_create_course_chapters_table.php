<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_chapters')) {
            Schema::create('course_chapters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('florida_courses')->onDelete('cascade');
                $table->string('title');
                $table->text('content');
                $table->string('video_url')->nullable();
                $table->integer('order_index');
                $table->integer('duration');
                $table->integer('required_min_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_chapters');
    }
};
