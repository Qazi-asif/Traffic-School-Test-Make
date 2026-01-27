<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('device_type', ['desktop', 'tablet', 'mobile']);
            $table->integer('screen_width');
            $table->integer('screen_height');
            $table->text('user_agent');
            $table->boolean('florida_course_accessed')->default(false);
            $table->timestamp('last_activity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_device_sessions');
    }
};
