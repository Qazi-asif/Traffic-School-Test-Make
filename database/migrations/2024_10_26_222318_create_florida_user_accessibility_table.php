<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('florida_user_accessibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('font_size', ['small', 'medium', 'large', 'xlarge'])->default('medium');
            $table->boolean('high_contrast_mode')->default(false);
            $table->boolean('reduced_animations')->default(false);
            $table->boolean('screen_reader_optimized')->default(false);
            $table->boolean('keyboard_navigation')->default(true);
            $table->boolean('mobile_optimized')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('florida_user_accessibility');
    }
};
