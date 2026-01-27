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
        Schema::create('timer_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timer_session_id')->constrained('timer_sessions')->onDelete('cascade');
            $table->string('violation_type', 50); // tab_switch, page_reload, window_blur, time_manipulation, etc.
            $table->text('details')->nullable();
            $table->timestamp('detected_at');
            $table->timestamps();
            
            $table->index(['timer_session_id', 'violation_type']);
            $table->index(['detected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timer_violations');
    }
};