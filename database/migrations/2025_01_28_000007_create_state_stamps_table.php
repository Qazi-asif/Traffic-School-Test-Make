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
        Schema::create('state_stamps', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 2);
            $table->string('stamp_name');
            $table->string('image_path');
            $table->string('image_url')->nullable();
            $table->integer('width')->default(80);
            $table->integer('height')->default(80);
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['state_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_stamps');
    }
};