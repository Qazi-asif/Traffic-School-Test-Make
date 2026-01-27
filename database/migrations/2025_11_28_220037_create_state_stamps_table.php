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
            $table->string('state_code', 2)->unique(); // e.g., 'FL', 'TX', 'CA'
            $table->string('state_name'); // e.g., 'Florida', 'Texas', 'California'
            $table->string('logo_path')->nullable(); // Path to uploaded logo
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
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
