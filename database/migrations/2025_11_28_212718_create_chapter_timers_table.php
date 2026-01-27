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
        Schema::create('chapter_timers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->string('chapter_type')->default('chapters'); // 'chapters' or 'florida_chapters'
            $table->integer('required_time_minutes');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('allow_pause')->default(true);
            $table->boolean('bypass_for_admin')->default(true);
            $table->timestamps();

            $table->index(['chapter_id', 'chapter_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_timers');
    }
};
